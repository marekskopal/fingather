<?php

declare(strict_types=1);

namespace FinGather\Service\Import;

use DateTimeImmutable;
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
use FinGather\Model\Repository\MarketRepository;
use FinGather\Service\Import\Entity\TransactionRecord;
use FinGather\Service\Import\Factory\ImportMapperFactoryInterface;
use FinGather\Service\Import\Factory\TransactionRecordFactoryInterface;
use FinGather\Service\Import\Mapper\MapperInterface;
use FinGather\Service\Provider\AssetProviderInterface;
use FinGather\Service\Provider\BrokerProviderInterface;
use FinGather\Service\Provider\CurrencyProviderInterface;
use FinGather\Service\Provider\DataProviderInterface;
use FinGather\Service\Provider\GroupProviderInterface;
use FinGather\Service\Provider\ImportFileProviderInterface;
use FinGather\Service\Provider\ImportMappingProviderInterface;
use FinGather\Service\Provider\ImportProviderInterface;
use FinGather\Service\Provider\SplitProviderInterface;
use FinGather\Service\Provider\TickerProviderInterface;
use FinGather\Service\Provider\TransactionProviderInterface;
use Psr\Log\LoggerInterface;

final readonly class ImportService
{
	public function __construct(
		private TransactionProviderInterface $transactionProvider,
		private TickerProviderInterface $tickerProvider,
		private AssetProviderInterface $assetProvider,
		private CurrencyProviderInterface $currencyProvider,
		private GroupProviderInterface $groupProvider,
		private DataProviderInterface $dataProvider,
		private ImportProviderInterface $importProvider,
		private ImportFileProviderInterface $importFileProvider,
		private ImportMappingProviderInterface $importMappingProvider,
		private BrokerProviderInterface $brokerProvider,
		private ImportMapperFactoryInterface $importMapperFactory,
		private TransactionRecordFactoryInterface $transactionRecordFactory,
		private SplitProviderInterface $splitProvider,
		private MarketRepository $marketRepository,
		private LoggerInterface $logger,
	) {
	}

	public function importDataFiles(Import $import): void
	{
		$user = $import->user;
		$portfolio = $import->portfolio;
		$othersGroup = $this->groupProvider->getOthersGroup($user, $portfolio);
		$defaultCurrency = $portfolio->currency;

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

		$this->dataProvider->deleteUserData($user, $portfolio, $firstDate);
	}

	private function importDataFile(
		ImportFile $importFile,
		User $user,
		Portfolio $portfolio,
		Group $othersGroup,
		Currency $defaultCurrency,
		?DateTimeImmutable $firstDate,
	): ?DateTimeImmutable {
		try {
			$importMapper = $this->importMapperFactory->createImportMapper(
				fileName: $importFile->fileName,
				contents: $importFile->contents,
			);
		} catch (\RuntimeException) {
			$this->logger->log('import', 'Import mapper not found');
			return null;
		}

		$broker = $this->brokerProvider->getBrokerByImportType($user, $portfolio, $importMapper->getImportType());
		if (!$broker instanceof Broker) {
			throw new \RuntimeException('Broker not found for import type ' . $importMapper->getImportType()->value . '.');
		}
		$importMappings = $this->importMappingProvider->getImportMappings($user, $portfolio, $broker);

		foreach ($importMapper->getRecords($importFile->contents) as $record) {
			/** @var array<string, string> $record */
			$transactionRecord = $this->transactionRecordFactory->createFromCsvRecord($importMapper, $record);

			if (
				isset($transactionRecord->importIdentifier)
				&& $this->transactionProvider->getTransactionByIdentifier($broker->id, $transactionRecord->importIdentifier) !== null
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
			$feeCurrency = $this->getCurrencyFromCode($transactionRecord->feeCurrency, $defaultCurrency);

			$actionType = TransactionActionTypeEnum::fromString($transactionRecord->actionType ?? '');

			$units = $transactionRecord->units ?? new Decimal(0);

			$price = $this->resolvePrice($transactionRecord, $units);

			$created = $transactionRecord->created ?? new DateTimeImmutable();

			if ($transactionRecord->isAdjusted === true) {
				$this->adjustTransaction($units, $price, $ticker, $created);
			}

			if ($actionType === TransactionActionTypeEnum::Sell) {
				$units = $units->negate();
			}

			$transaction = $this->transactionProvider->createTransaction(
				user: $user,
				portfolio: $portfolio,
				asset: $asset,
				broker: $broker,
				actionType: $actionType,
				actionCreated: $created,
				createType: TransactionCreateTypeEnum::Import,
				units: $units,
				price: $price,
				currency: $currency,
				tax: $transactionRecord->tax,
				taxCurrency: $taxCurrency,
				fee: $transactionRecord->fee,
				feeCurrency: $feeCurrency,
				notes: $transactionRecord->notes,
				importIdentifier: $transactionRecord->importIdentifier,
			);

			if ($firstDate === null || $transaction->actionCreated->getTimestamp() < $firstDate->getTimestamp()) {
				$firstDate = $transaction->actionCreated;
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
			return $importMappings[$tickerKey]->ticker;
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

		$marketIds = $this->resolveMarketIds($transactionRecord, $importMapper);

		$ticker = $this->tickerProvider->getTickerByTicker(
			ticker: $transactionRecord->ticker,
			isin: $transactionRecord->isin,
			marketIds: $marketIds,
		);
		if ($ticker === null) {
			$ticker = $this->tickerProvider->getTickerByTicker(
				ticker: $transactionRecord->ticker,
				marketIds: $marketIds,
			);
		}
		// Country scoping is a hint, not a hard filter — fall back to the mapper-level
		// allowed markets if the country-restricted lookup found nothing.
		if ($ticker === null && $marketIds !== $importMapper->getAllowedMarketIds()) {
			$ticker = $this->tickerProvider->getTickerByTicker(
				ticker: $transactionRecord->ticker,
				marketIds: $importMapper->getAllowedMarketIds(),
			);
		}

		return $ticker;
	}

	/** @return list<int>|null */
	private function resolveMarketIds(TransactionRecord $transactionRecord, MapperInterface $importMapper): ?array
	{
		$mapperAllowed = $importMapper->getAllowedMarketIds();
		if ($transactionRecord->country === null) {
			return $mapperAllowed;
		}

		$countryIds = $this->marketRepository->findMarketIdsByCountry($transactionRecord->country);
		if (count($countryIds) === 0) {
			return $mapperAllowed;
		}

		if ($mapperAllowed === null) {
			return $countryIds;
		}

		$intersect = array_values(array_intersect($mapperAllowed, $countryIds));
		return count($intersect) > 0 ? $intersect : $mapperAllowed;
	}

	private function getTickerKey(TransactionRecord $transactionRecord, Broker $broker): ?string
	{
		if ($transactionRecord->ticker === null && $transactionRecord->isin !== null) {
			return $broker->id . '-' . $transactionRecord->isin;
		}

		if ($transactionRecord->ticker === null) {
			return null;
		}

		return $broker->id . '-' . $transactionRecord->ticker;
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
		return $this->currencyProvider->getCurrencyByCode($code);
	}

	private function resolvePrice(TransactionRecord $transactionRecord, Decimal $units): ?Decimal
	{
		if ($transactionRecord->price !== null || $transactionRecord->total === null) {
			return $transactionRecord->price;
		}
		// Avoid division by zero in dividends
		return $units->isZero() ? $transactionRecord->total : $transactionRecord->total->div($units);
	}

	private function adjustTransaction(Decimal &$units, ?Decimal &$price, Ticker $ticker, DateTimeImmutable $created): void
	{
		$splits = $this->splitProvider->getSplits($ticker);
		foreach ($splits as $split) {
			if ($split->date <= $created) {
				continue;
			}

			$units = $units->div($split->factor);
			$price = $price?->mul($split->factor);
		}
	}
}
