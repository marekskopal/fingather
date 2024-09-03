<?php

declare(strict_types=1);

namespace FinGather\Service\Import;

use Decimal\Decimal;
use FinGather\Model\Entity\Broker;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Enum\TransactionCreateTypeEnum;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Import;
use FinGather\Model\Entity\ImportFile;
use FinGather\Model\Entity\ImportMapping;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\CurrencyRepository;
use FinGather\Model\Repository\TransactionRepository;
use FinGather\Service\Import\Entity\TransactionRecord;
use FinGather\Service\Import\Factory\ImportMapperFactory;
use FinGather\Service\Import\Factory\TransactionRecordFactory;
use FinGather\Service\Import\Mapper\MapperInterface;
use FinGather\Service\Provider\AssetProvider;
use FinGather\Service\Provider\BrokerProvider;
use FinGather\Service\Provider\DataProvider;
use FinGather\Service\Provider\GroupProvider;
use FinGather\Service\Provider\ImportFileProvider;
use FinGather\Service\Provider\ImportMappingProvider;
use FinGather\Service\Provider\ImportProvider;
use FinGather\Service\Provider\TickerProvider;
use FinGather\Service\Provider\TransactionProvider;
use Psr\Log\LoggerInterface;
use Safe\DateTimeImmutable;

final class ImportService
{
	public function __construct(
		private readonly TransactionRepository $transactionRepository,
		private readonly TransactionProvider $transactionProvider,
		private readonly TickerProvider $tickerProvider,
		private readonly AssetProvider $assetProvider,
		private readonly CurrencyRepository $currencyRepository,
		private readonly GroupProvider $groupProvider,
		private readonly DataProvider $dataProvider,
		private readonly ImportProvider $importProvider,
		private readonly ImportFileProvider $importFileProvider,
		private readonly ImportMappingProvider $importMappingProvider,
		private readonly BrokerProvider $brokerProvider,
		private readonly ImportMapperFactory $importMapperFactory,
		private readonly TransactionRecordFactory $transactionRecordFactory,
		private readonly LoggerInterface $logger,
	) {
	}

	public function importDataFiles(Import $import): void
	{
		$user = $import->getUser();
		$portfolio = $import->getPortfolio();
		$othersGroup = $this->groupProvider->getOthersGroup($user, $portfolio);
		$defaultCurrency = $portfolio->getCurrency();

		$firstDate = null;

		$importFiles = $this->importFileProvider->getImportFiles($import);
		foreach ($importFiles as $importFile) {
			$firstDateDataFile = $this->importDataFile(
				importFile: $importFile,
				user: $user,
				portfolio: $portfolio,
				othersGroup: $othersGroup,
				defaultCurrency: $defaultCurrency,
				firstDate: $firstDate,
			);

			if ($firstDateDataFile === null) {
				continue;
			}

			$firstDate = $firstDateDataFile;
		}

		$this->importProvider->deleteImport($import);

		if ($firstDate === null) {
			return;
		}

		$this->dataProvider->deleteUserData($user, $portfolio, DateTimeImmutable::createFromRegular($firstDate));
	}

	private function importDataFile(
		ImportFile $importFile,
		User $user,
		Portfolio $portfolio,
		Group $othersGroup,
		Currency $defaultCurrency,
		?\DateTimeImmutable $firstDate,
	): ?\DateTimeImmutable {
		try {
			$importMapper = $this->importMapperFactory->createImportMapper(
				fileName: $importFile->getFileName(),
				contents: $importFile->getContents(),
			);
		} catch (\RuntimeException) {
			$this->logger->log('import', 'Import mapper not found');
			return null;
		}

		$broker = $this->brokerProvider->getBrokerByImportType($user, $portfolio, $importMapper->getImportType());
		assert($broker instanceof Broker);
		$importMappings = $this->importMappingProvider->getImportMappings($user, $portfolio, $broker);

		foreach ($importMapper->getRecords($importFile->getContents()) as $record) {
			/** @var array<string, string> $record */
			$transactionRecord = $this->transactionRecordFactory->createFromCsvRecord($importMapper, $record);

			if (
				isset($transactionRecord->importIdentifier)
				&& $this->transactionRepository->findTransactionByIdentifier(
					$broker->getId(),
					$transactionRecord->importIdentifier,
				) !== null
			) {
				$this->logger->log('import', 'Skipped transaction: ' . implode(',', $record));
				continue;
			}

			$ticker = $this->getTickerFromTransactionRecord($transactionRecord, $broker, $importMapper, $importMappings);
			if ($ticker === null) {
				$this->logger->log('import', 'Ticker not found: ' . implode(',', $record));
				continue;
			}

			$asset = $this->assetProvider->getOrCreateAsset($user, $portfolio, $ticker, $othersGroup);

			$currency = $this->getCurrencyFromCodeNullable($transactionRecord->currency, $defaultCurrency);
			if ($currency === null) {
				continue;
			}

			$taxCurrency = $this->getCurrencyFromCode($transactionRecord->taxCurrency, $defaultCurrency);
			$feeCurrency = $this->getCurrencyFromCode($transactionRecord->taxCurrency, $defaultCurrency);

			$actionType = TransactionActionTypeEnum::fromString($transactionRecord->actionType ?? '');

			$units = $transactionRecord->units ?? new Decimal(0);
			if ($actionType === TransactionActionTypeEnum::Sell) {
				$units = $units->negate();
			}

			$transaction = $this->transactionProvider->createTransaction(
				user: $user,
				portfolio: $portfolio,
				asset: $asset,
				broker: $broker,
				actionType: $actionType,
				actionCreated: $transactionRecord->created ?? new DateTimeImmutable(),
				createType: TransactionCreateTypeEnum::Import,
				units: $units,
				price: $transactionRecord->price,
				currency: $currency,
				tax: $transactionRecord->tax,
				taxCurrency: $taxCurrency,
				fee: $transactionRecord->fee,
				feeCurrency: $feeCurrency,
				notes: $transactionRecord->notes,
				importIdentifier: $transactionRecord->importIdentifier,
			);

			if ($firstDate === null || $transaction->getActionCreated()->getTimestamp() < $firstDate->getTimestamp()) {
				$firstDate = $transaction->getActionCreated();
			}
		}

		return $firstDate;
	}

	/** @param array<string, ImportMapping> $importMappings */
	private function getTickerFromTransactionRecord(
		TransactionRecord $transactionRecord,
		Broker $broker,
		MapperInterface $importMapper,
		array $importMappings,
	): ?Ticker
	{
		$tickerKey = $this->getTickerKey($transactionRecord, $broker);
		if ($tickerKey === null) {
			return null;
		}

		if (array_key_exists($tickerKey, $importMappings)) {
			return $importMappings[$tickerKey]->getTicker();
		}

		if ($transactionRecord->ticker === null && $transactionRecord->isin !== null) {
			$ticker = $this->tickerProvider->getTickerByIsin(
				isin: $transactionRecord->isin,
				marketIds: $importMapper->getAllowedMarketIds(),
			);
			if ($ticker !== null) {
				return $ticker;
			}
		}

		if ($transactionRecord->ticker === null) {
			return null;
		}

		$ticker = $this->tickerProvider->getTickerByTicker(
			ticker: $transactionRecord->ticker,
			isin: $transactionRecord->isin,
			marketIds: $importMapper->getAllowedMarketIds(),
		);
		if ($ticker === null) {
			$ticker = $this->tickerProvider->getTickerByTicker(
				ticker: $transactionRecord->ticker,
				marketIds: $importMapper->getAllowedMarketIds(),
			);
		}

		return $ticker;
	}

	private function getTickerKey(TransactionRecord $transactionRecord, Broker $broker): ?string
	{
		if ($transactionRecord->ticker === null && $transactionRecord->isin !== null) {
			return $broker->getId() . '-' . $transactionRecord->isin;
		}

		if ($transactionRecord->ticker === null) {
			return null;
		}

		return $broker->getId() . '-' . $transactionRecord->ticker;
	}

	private function getCurrencyFromCode(?string $code, Currency $defaultCurrency): Currency
	{
		$currency = $this->getCurrencyFromCodeNullable($code, $defaultCurrency);
		if ($currency === null) {
			return $defaultCurrency;
		}

		return $currency;
	}

	private function getCurrencyFromCodeNullable(?string $code, Currency $defaultCurrency): ?Currency
	{
		if ($code === null) {
			return $defaultCurrency;
		}
		return $this->currencyRepository->findCurrencyByCode($code);
	}
}
