<?php

declare(strict_types=1);

namespace FinGather\Service\Import;

use Cycle\Database\Exception\StatementException\ConstrainException;
use FinGather\Dto\ImportDataFileDto;
use FinGather\Dto\ImportPrepareDataDto;
use FinGather\Model\Entity\Import;
use FinGather\Model\Entity\ImportMapping;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\User;
use FinGather\Service\Import\Entity\PrepareImport;
use FinGather\Service\Import\Entity\PrepareImportTicker;
use FinGather\Service\Import\Entity\TransactionRecord;
use FinGather\Service\Import\Factory\ImportMapperFactory;
use FinGather\Service\Import\Factory\TransactionRecordFactory;
use FinGather\Service\Import\Mapper\MapperInterface;
use FinGather\Service\Provider\BrokerProvider;
use FinGather\Service\Provider\ImportFileProvider;
use FinGather\Service\Provider\ImportMappingProvider;
use FinGather\Service\Provider\ImportProvider;
use FinGather\Service\Provider\TickerProvider;
use FinGather\Service\Update\TickerIsinUpdater;
use Psr\Log\LoggerInterface;

final class ImportPrepareService
{
	public function __construct(
		private readonly TickerProvider $tickerProvider,
		private readonly TickerIsinUpdater $tickerIsinUpdater,
		private readonly ImportProvider $importProvider,
		private readonly ImportFileProvider $importFileProvider,
		private readonly ImportMappingProvider $importMappingProvider,
		private readonly BrokerProvider $brokerProvider,
		private readonly ImportMapperFactory $importMapperFactory,
		private readonly TransactionRecordFactory $transactionRecordFactory,
		private readonly LoggerInterface $logger,
	) {
	}

	public function prepareImport(User $user, Portfolio $portfolio, ImportPrepareDataDto $importPrepareData): PrepareImport
	{
		$notFoundTickers = [];
		$multipleFoundTickers = [];
		$okFoundTickers = [];

		$importMapper = $this->importMapperFactory->createImportMapper(
			fileName: $importPrepareData->importDataFile->fileName,
			contents: $importPrepareData->importDataFile->contents,
		);

		$broker = $this->brokerProvider->getBrokerByImportType($user, $portfolio, $importMapper->getImportType());
		if ($broker === null) {
			$broker = $this->brokerProvider->createBroker(
				user: $user,
				portfolio: $portfolio,
				name: $importMapper->getImportType()->value,
				importType: $importMapper->getImportType(),
			);
		}
		$brokerId = $broker->getId();

		$importMappings = $this->importMappingProvider->getImportMappings($user, $portfolio, $broker);

		$transactionRecords = $this->getTransactionRecords($importMapper, $importPrepareData->importDataFile);

		$this->createIsinsFromTransactionRecords($transactionRecords);

		$this->prepareImportTickers(
			transactionRecords: $transactionRecords,
			importMapper: $importMapper,
			brokerId: $brokerId,
			importMappings: $importMappings,
			okFoundTickers: $okFoundTickers,
			notFoundTickers: $notFoundTickers,
			multipleFoundTickers: $multipleFoundTickers,
		);

		$import = $this->importProvider->getImportByUuid($user, $importPrepareData->uuid);
		if ($import === null) {
			try {
				$import = $this->importProvider->createImport(user: $user, portfolio: $portfolio, uuid: $importPrepareData->uuid);
			} catch (ConstrainException) {
				// Import with this UUID already exists
				$import = $this->importProvider->getImportByUuid($user, $importPrepareData->uuid);
				assert($import instanceof Import);
			}
		}

		$importFile = $this->importFileProvider->createImportFile(
			import: $import,
			fileName: $importPrepareData->importDataFile->fileName,
			contents: $importPrepareData->importDataFile->contents,
		);

		return new PrepareImport(
			import: $import,
			importFile: $importFile,
			notFoundTickers: $notFoundTickers,
			multipleFoundTickers: $multipleFoundTickers,
			okFoundTickers: $okFoundTickers,
		);
	}

	/** @return list<TransactionRecord> */
	private function getTransactionRecords(MapperInterface $importMapper, ImportDataFileDto $importDataFile): array
	{
		$transactionRecords = [];
		foreach ($importMapper->getRecords($importDataFile->contents) as $record) {
			/** @var array<string, string> $record */
			$transactionRecord = $this->transactionRecordFactory->createFromCsvRecord($importMapper, $record);

			if (!isset($transactionRecord->ticker) && !isset($transactionRecord->isin)) {
				$this->logger->log('import', 'Ticker or ISIN not found: ' . implode(',', $record));
				continue;
			}

			$transactionRecords[] = $transactionRecord;
		}

		return $transactionRecords;
	}

	/** @param list<TransactionRecord> $transactionRecords */
	private function createIsinsFromTransactionRecords(array $transactionRecords): void
	{
		$isins = [];
		foreach ($transactionRecords as $transactionRecord) {
			if (!isset($transactionRecord->isin)) {
				continue;
			}

			if ($this->tickerProvider->getTickerByIsin($transactionRecord->isin) !== null) {
				continue;
			}

			$isins[] = $transactionRecord->isin;
		}

		if (count($isins) === 0) {
			return;
		}

		$this->tickerIsinUpdater->updateTickerIsins($isins);
	}

	/**
	 * @param list<TransactionRecord> $transactionRecords
	 * @param array<string, ImportMapping> $importMappings
	 * @param array<string, PrepareImportTicker> $notFoundTickers
	 * @param array<string, PrepareImportTicker> $multipleFoundTickers
	 * @param array<string, PrepareImportTicker> $okFoundTickers
	 */
	private function prepareImportTickers(
		array $transactionRecords,
		MapperInterface $importMapper,
		int $brokerId,
		array $importMappings,
		array &$okFoundTickers,
		array &$multipleFoundTickers,
		array &$notFoundTickers,
	): void
	{
		foreach ($transactionRecords as $transactionRecord) {
			if ($transactionRecord->ticker === null && $transactionRecord->isin !== null) {
				$this->prepareImportTickersFromIsin(
					isin: $transactionRecord->isin,
					importMapper: $importMapper,
					brokerId: $brokerId,
					importMappings: $importMappings,
					okFoundTickers: $okFoundTickers,
					multipleFoundTickers: $multipleFoundTickers,
					notFoundTickers: $notFoundTickers,
				);
				continue;
			}

			if ($transactionRecord->ticker === null) {
				continue;
			}

			$this->prepareImportTickersFromTicker(
				ticker: $transactionRecord->ticker,
				isin: $transactionRecord->isin,
				importMapper: $importMapper,
				brokerId: $brokerId,
				importMappings: $importMappings,
				okFoundTickers: $okFoundTickers,
				multipleFoundTickers: $multipleFoundTickers,
				notFoundTickers: $notFoundTickers,
			);
		}
	}

	/**
	 * @param array<string, ImportMapping> $importMappings
	 * @param array<string, PrepareImportTicker> $notFoundTickers
	 * @param array<string, PrepareImportTicker> $multipleFoundTickers
	 * @param array<string, PrepareImportTicker> $okFoundTickers
	 */
	public function prepareImportTickersFromTicker(
		string $ticker,
		?string $isin,
		MapperInterface $importMapper,
		int $brokerId,
		array $importMappings,
		array &$okFoundTickers,
		array &$multipleFoundTickers,
		array &$notFoundTickers,
	): void
	{
		$tickerKey = $brokerId . '-' . $ticker;

		if (array_key_exists($tickerKey, $notFoundTickers)) {
			return;
		}
		if (array_key_exists($tickerKey, $multipleFoundTickers)) {
			return;
		}
		if (array_key_exists($tickerKey, $okFoundTickers)) {
			return;
		}

		if (array_key_exists($tickerKey, $importMappings)) {
			$okFoundTickers[$tickerKey] = new PrepareImportTicker(
				brokerId: $brokerId,
				ticker: $ticker,
				tickers: [$importMappings[$tickerKey]->getTicker()],
			);
			return;
		}

		$countTicker = $this->tickerProvider->countTickersByTicker(
			ticker: $ticker,
			isin: $isin,
			marketIds: $importMapper->getAllowedMarketIds(),
		);
		if ($countTicker === 0 && $isin !== null) {
			$isin = null;
			$countTicker = $this->tickerProvider->countTickersByTicker(
				ticker: $ticker,
				isin: $isin,
				marketIds: $importMapper->getAllowedMarketIds(),
			);
		}
		if ($countTicker === 0) {
			$notFoundTickers[$tickerKey] = new PrepareImportTicker(
				brokerId: $brokerId,
				ticker: $ticker,
				tickers: [],
			);
		} elseif ($countTicker > 1) {
			$multipleFoundTickers[$tickerKey] = new PrepareImportTicker(
				brokerId: $brokerId,
				ticker: $ticker,
				tickers: $this->tickerProvider->getTickersByTicker(
					ticker: $ticker,
					isin: $isin,
					marketIds: $importMapper->getAllowedMarketIds(),
				),
			);
		} else {
			$tickerByTicker = $this->tickerProvider->getTickerByTicker(
				ticker: $ticker,
				isin: $isin,
				marketIds: $importMapper->getAllowedMarketIds(),
			);
			assert($tickerByTicker instanceof Ticker);
			$okFoundTickers[$tickerKey] = new PrepareImportTicker(
				brokerId: $brokerId,
				ticker: $ticker,
				tickers: [$tickerByTicker],
			);
		}
	}

	/**
	 * @param array<string, ImportMapping> $importMappings
	 * @param array<string, PrepareImportTicker> $notFoundTickers
	 * @param array<string, PrepareImportTicker> $multipleFoundTickers
	 * @param array<string, PrepareImportTicker> $okFoundTickers
	 */
	public function prepareImportTickersFromIsin(
		string $isin,
		MapperInterface $importMapper,
		int $brokerId,
		array $importMappings,
		array &$okFoundTickers,
		array &$multipleFoundTickers,
		array &$notFoundTickers,
	): void
	{
		$tickerKey = $brokerId . '-' . $isin;

		if (array_key_exists($tickerKey, $notFoundTickers)) {
			return;
		}
		if (array_key_exists($tickerKey, $multipleFoundTickers)) {
			return;
		}
		if (array_key_exists($tickerKey, $okFoundTickers)) {
			return;
		}

		if (array_key_exists($tickerKey, $importMappings)) {
			$okFoundTickers[$tickerKey] = new PrepareImportTicker(
				brokerId: $brokerId,
				ticker: $isin,
				tickers: [$importMappings[$tickerKey]->getTicker()],
			);
			return;
		}

		$countTicker = $this->tickerProvider->countTickersByIsin(
			isin: $isin,
			marketIds: $importMapper->getAllowedMarketIds(),
		);
		if ($countTicker === 0) {
			$notFoundTickers[$tickerKey] = new PrepareImportTicker(
				brokerId: $brokerId,
				ticker: $isin,
				tickers: [],
			);
		} elseif ($countTicker > 1) {
			$multipleFoundTickers[$tickerKey] = new PrepareImportTicker(
				brokerId: $brokerId,
				ticker: $isin,
				tickers: $this->tickerProvider->getTickersByIsin(
					isin: $isin,
					marketIds: $importMapper->getAllowedMarketIds(),
				),
			);
		} else {
			$tickerByTicker = $this->tickerProvider->getTickerByIsin(
				isin: $isin,
				marketIds: $importMapper->getAllowedMarketIds(),
			);
			assert($tickerByTicker instanceof Ticker);
			$okFoundTickers[$tickerKey] = new PrepareImportTicker(
				brokerId: $brokerId,
				ticker: $isin,
				tickers: [$tickerByTicker],
			);
		}
	}
}
