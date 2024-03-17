<?php

declare(strict_types=1);

namespace FinGather\Service\Import;

use Decimal\Decimal;
use FinGather\Dto\ImportDataDto;
use FinGather\Dto\ImportDataFileDto;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Broker;
use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Enum\TransactionCreateTypeEnum;
use FinGather\Model\Entity\Import;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\AssetRepository;
use FinGather\Model\Repository\CurrencyRepository;
use FinGather\Model\Repository\TransactionRepository;
use FinGather\Service\Import\Entity\PrepareImport;
use FinGather\Service\Import\Entity\PrepareImportTicker;
use FinGather\Service\Import\Entity\TransactionRecord;
use FinGather\Service\Import\Mapper\AnycoinMapper;
use FinGather\Service\Import\Mapper\EtoroMapper;
use FinGather\Service\Import\Mapper\InteractiveBrokersMapper;
use FinGather\Service\Import\Mapper\MapperInterface;
use FinGather\Service\Import\Mapper\RevolutMapper;
use FinGather\Service\Import\Mapper\Trading212Mapper;
use FinGather\Service\Import\Mapper\XtbMapper;
use FinGather\Service\Provider\BrokerProvider;
use FinGather\Service\Provider\DataProvider;
use FinGather\Service\Provider\GroupProvider;
use FinGather\Service\Provider\ImportMappingProvider;
use FinGather\Service\Provider\ImportProvider;
use FinGather\Service\Provider\TickerProvider;
use FinGather\Service\Provider\TransactionProvider;
use Psr\Log\LoggerInterface;
use Safe\DateTimeImmutable;
use function Safe\json_encode;

final class ImportService
{
	private const ImportMappers = [
		BrokerImportTypeEnum::Trading212->value => Trading212Mapper::class,
		BrokerImportTypeEnum::InteractiveBrokers->value => InteractiveBrokersMapper::class,
		BrokerImportTypeEnum::Xtb->value => XtbMapper::class,
		BrokerImportTypeEnum::Etoro->value => EtoroMapper::class,
		BrokerImportTypeEnum::Revolut->value => RevolutMapper::class,
		BrokerImportTypeEnum::Anycoin->value => AnycoinMapper::class,
	];

	public function __construct(
		private readonly TransactionRepository $transactionRepository,
		private readonly TransactionProvider $transactionProvider,
		private readonly TickerProvider $tickerProvider,
		private readonly AssetRepository $assetRepository,
		private readonly CurrencyRepository $currencyRepository,
		private readonly GroupProvider $groupProvider,
		private readonly DataProvider $dataProvider,
		private readonly ImportProvider $importProvider,
		private readonly ImportMappingProvider $importMappingProvider,
		private readonly BrokerProvider $brokerProvider,
		private readonly LoggerInterface $logger,
	) {
	}

	public function prepareImport(User $user, Portfolio $portfolio, ImportDataDto $importData): PrepareImport
	{
		$notFoundTickers = [];
		$multipleFoundTickers = [];
		$okFoundTickers = [];

		foreach ($importData->importDataFiles as $importDataFile) {
			try {
				$importMapper = $this->getImportMapper($importDataFile);
			} catch (\RuntimeException) {
				$this->logger->log('import', 'Import mapper not found');
				continue;
			}

			$broker = $this->brokerProvider->getBrokerByImportType($user, $portfolio, $importMapper->getImportType());
			if ($broker === null) {
				$broker = $this->brokerProvider->createBroker(
					user: $user,
					portfolio: $portfolio,
					name: $importMapper->getImportType()->value,
					importType: $importMapper->getImportType(),
				);
			}
			$importMappings = $this->importMappingProvider->getImportMappings($user, $portfolio, $broker);

			foreach ($importMapper->getRecords($importDataFile->contents) as $record) {
				/** @var array<string, string> $record */
				$transactionRecord = $this->mapTransactionRecord($importMapper, $record);

				if (!isset($transactionRecord->ticker)) {
					$this->logger->log('import', 'Ticker not found: ' . implode(',', $record));
					continue;
				}

				if (array_key_exists($transactionRecord->ticker, $notFoundTickers)) {
					continue;
				}
				if (array_key_exists($transactionRecord->ticker, $multipleFoundTickers)) {
					continue;
				}
				if (array_key_exists($transactionRecord->ticker, $okFoundTickers)) {
					continue;
				}

				if (array_key_exists($transactionRecord->ticker, $importMappings)) {
					$okFoundTickers[$transactionRecord->ticker] = new PrepareImportTicker(
						brokerId: $broker->getId(),
						ticker: $transactionRecord->ticker,
						tickers: [$importMappings[$transactionRecord->ticker]->getTicker()],
					);
					continue;
				}

				$countTicker = $this->tickerProvider->countTickersByTicker($transactionRecord->ticker);
				if ($countTicker === 0) {
					$notFoundTickers[$transactionRecord->ticker] = new PrepareImportTicker(ticker: $transactionRecord->ticker, tickers: []);
				} elseif ($countTicker > 1) {
					$multipleFoundTickers[$transactionRecord->ticker] = new PrepareImportTicker(
						brokerId: $broker->getId(),
						ticker: $transactionRecord->ticker,
						tickers: $this->tickerProvider->getTickersByTicker($transactionRecord->ticker),
					);
				} else {
					$tickerByTicker = $this->tickerProvider->getTickerByTicker($transactionRecord->ticker);
					assert($tickerByTicker instanceof Ticker);
					$okFoundTickers[$transactionRecord->ticker] = new PrepareImportTicker(
						brokerId: $broker->getId(),
						ticker: $transactionRecord->ticker,
						tickers: [$tickerByTicker],
					);
				}
			}
		}

		$import = $this->importProvider->createImport(
			user: $user,
			portfolio: $portfolio,
			csvContent: json_encode($importData),
		);

		return new PrepareImport(
			import: $import,
			notFoundTickers: $notFoundTickers,
			multipleFoundTickers: $multipleFoundTickers,
			okFoundTickers: $okFoundTickers,
		);
	}

	public function importCsv(Import $import): void
	{
		$user = $import->getUser();
		$portfolio = $import->getPortfolio();
		$othersGroup = $this->groupProvider->getOthersGroup($user, $portfolio);
		$defaultCurrency = $user->getDefaultCurrency();

		$firstDate = null;

		$importData = ImportDataDto::fromJson($import->getCsvContent());
		foreach ($importData->importDataFiles as $importDataFile) {
			try {
				$importMapper = $this->getImportMapper($importDataFile);
			} catch (\RuntimeException) {
				$this->logger->log('import', 'Import mapper not found');
				continue;
			}

			$broker = $this->brokerProvider->getBrokerByImportType($user, $portfolio, $importMapper->getImportType());
			assert($broker instanceof Broker);
			$importMappings = $this->importMappingProvider->getImportMappings($user, $portfolio, $broker);

			foreach ($importMapper->getRecords($importDataFile->contents) as $record) {
				/** @var array<string, string> $record */
				$transactionRecord = $this->mapTransactionRecord($importMapper, $record);

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

				if (!isset($transactionRecord->ticker)) {
					$this->logger->log('import', 'Ticker not found: ' . implode(',', $record));
					continue;
				}

				$ticker = array_key_exists($transactionRecord->ticker, $importMappings)
					? $importMappings[$transactionRecord->ticker]->getTicker()
					: $this->tickerProvider->getTickerByTicker($transactionRecord->ticker);
				if ($ticker === null) {
					$this->logger->log('import', 'Ticker not created: ' . implode(',', $record));
					continue;
				}

				$asset = $this->assetRepository->findAssetByTickerId($user->getId(), $portfolio->getId(), $ticker->getId());
				if ($asset === null) {
					$asset = new Asset(
						user: $user,
						portfolio: $portfolio,
						ticker: $ticker,
						group: $othersGroup,
						transactions: [],
					);
					$this->assetRepository->persist($asset);
				}

				if ($transactionRecord->currency === null) {
					$currency = $ticker->getCurrency();
				} else {
					$currency = $this->currencyRepository->findCurrencyByCode($transactionRecord->currency);
					if ($currency === null) {
						continue;
					}
				}

				$taxCurrencyCode = $transactionRecord->taxCurrency;
				if ($taxCurrencyCode === null) {
					$taxCurrency = $defaultCurrency;
				} else {
					$taxCurrency = $this->currencyRepository->findCurrencyByCode($taxCurrencyCode);
					if ($taxCurrency === null) {
						$taxCurrency = $defaultCurrency;
					}
				}

				$feeCurrencyCode = $transactionRecord->taxCurrency;
				if ($feeCurrencyCode === null) {
					$feeCurrency = $defaultCurrency;
				} else {
					$feeCurrency = $this->currencyRepository->findCurrencyByCode($feeCurrencyCode);
					if ($feeCurrency === null) {
						$feeCurrency = $defaultCurrency;
					}
				}

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
		}

		$this->importProvider->deleteImport($import);

		if ($firstDate === null) {
			return;
		}

		$this->dataProvider->deleteUserData($user, $portfolio, DateTimeImmutable::createFromRegular($firstDate));
	}

	/** @param array<string, string> $csvRecord */
	private function mapTransactionRecord(MapperInterface $mapper, array $csvRecord): TransactionRecord
	{
		$mappedRecord = [];

		foreach ($mapper->getMapping() as $attribute => $recordKey) {
			if ($recordKey === null) {
				$mappedRecord[$attribute] = null;
				continue;
			}

			if (!is_string($recordKey)) {
				$mappedRecord[$attribute] = $recordKey($csvRecord);
				continue;
			}

			$mappedRecord[$attribute] = $csvRecord[$recordKey] ?? null;
		}

		$ticker = ($mappedRecord['ticker'] ?? '') !== '' ? ($mappedRecord['ticker'] ?? null) : null;

		return new TransactionRecord(
			ticker: $ticker,
			marketMic: $mappedRecord['marketMic'] ? strtoupper($mappedRecord['marketMic']) : null,
			actionType: strtolower($mappedRecord['actionType'] ?? ''),
			created: new DateTimeImmutable($mappedRecord['created'] ?? ''),
			units: $mappedRecord['units'] ? new Decimal($mappedRecord['units']) : null,
			price: $mappedRecord['price'] ? new Decimal($mappedRecord['price']) : null,
			currency: $mappedRecord['currency'],
			tax: $mappedRecord['tax'] ? new Decimal($mappedRecord['tax']) : null,
			taxCurrency: $mappedRecord['taxCurrency'] ?? null,
			fee: $mappedRecord['fee'] ? new Decimal($mappedRecord['fee']) : null,
			feeCurrency: $mappedRecord['feeCurrency'] ?? null,
			notes: $mappedRecord['notes'] ?? null,
			importIdentifier: ($mappedRecord['importIdentifier'] ?? '') !== '' ? ($mappedRecord['importIdentifier'] ?? null) : null,
		);
	}

	private function getImportMapper(ImportDataFileDto $importDataFile): MapperInterface
	{
		foreach (self::ImportMappers as $mapperClass) {
			$importMapper = new $mapperClass();
			if ($importMapper->check($importDataFile->contents, $importDataFile->fileName)) {
				return $importMapper;
			}
		}

		throw new \RuntimeException('Import mapper not found');
	}
}
