<?php

declare(strict_types=1);

namespace FinGather\Service\Import;

use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Broker;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Dividend;
use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Repository\AssetRepository;
use FinGather\Model\Repository\CurrencyRepository;
use FinGather\Model\Repository\DividendRepository;
use FinGather\Model\Repository\GroupDataRepository;
use FinGather\Model\Repository\GroupRepository;
use FinGather\Model\Repository\PortfolioDataRepository;
use FinGather\Model\Repository\TransactionRepository;
use FinGather\Service\Import\Entity\TransactionRecord;
use FinGather\Service\Import\Mapper\AnycoinMapper;
use FinGather\Service\Import\Mapper\MapperInterface;
use FinGather\Service\Import\Mapper\RevolutMapper;
use FinGather\Service\Import\Mapper\Trading212Mapper;
use FinGather\Service\Provider\TickerProvider;
use League\Csv\Reader;
use Psr\Log\LoggerInterface;
use Safe\DateTimeImmutable;

final class ImportService
{
	public function __construct(
		private readonly TransactionRepository $transactionRepository,
		private readonly TickerProvider $tickerProvider,
		private readonly AssetRepository $assetRepository,
		private readonly CurrencyRepository $currencyRepository,
		private readonly DividendRepository $dividendRepository,
		private readonly PortfolioDataRepository $portfolioDataRepository,
		private readonly GroupDataRepository $groupDataRepository,
		private readonly GroupRepository $groupRepository,
		private readonly LoggerInterface $logger,
	) {
	}

	public function importCsv(Broker $broker, string $csvContent): void
	{
		$csv = Reader::createFromString($csvContent);
		$csv->setHeaderOffset(0);

		$importMapper = $this->getImportMapper(BrokerImportTypeEnum::from($broker->getImportType()));
		$tickerMapping = $importMapper->getTickerMapping();

		$user = $broker->getUser();
		$othersGroup = $this->groupRepository->findOthersGroup($user->getId());
		$defaultCurrency = $user->getDefaultCurrency();

		$firstDate = null;

		$records = $csv->getRecords();
		foreach ($records as $record) {
			/** @var array<string, string> $record */
			$transactionRecord = $this->mapTransactionRecord($importMapper, $record);

			if (
				isset($transactionRecord->importIdentifier)
				&& $this->transactionRepository->findTransactionByIdentifier(
					$broker->getId(),
					$transactionRecord->importIdentifier
				) !== null
			) {
				$this->logger->log('import', 'Skipped transaction: ' . implode(',', $record));
				continue;
			}

			if (!isset($transactionRecord->ticker)) {
				$this->logger->log('import', 'Ticker not found: ' . implode(',', $record));
				continue;
			}

			$transactionRecordTicker = array_search($transactionRecord->ticker, $tickerMapping, strict: true);
			if ($transactionRecordTicker === false) {
				$transactionRecordTicker = $transactionRecord->ticker;
			}

			$ticker = $this->tickerProvider->getOrCreateTicker($transactionRecordTicker);
			if ($ticker === null) {
				$this->logger->log('import', 'Ticker not created: ' . implode(',', $record));
				continue;
			}

			$asset = $this->assetRepository->findAssetByTickerId($user->getId(), $ticker->getId());
			if ($asset === null) {
				$asset = new Asset(
					user: $user,
					ticker: $ticker,
					group: $othersGroup,
					transactions: [],
				);
				$this->assetRepository->persist($asset);
			}

			$currencyCode = $transactionRecord->currency ?? 'USD';
			$currency = $this->currencyRepository->findCurrencyByCode($currencyCode);
			assert($currency instanceof Currency);

			if (strpos($transactionRecord->actionType ?? '', 'dividend') !== false) {
				if ($transactionRecord->dividendCurrency !== null) {
					$dividendCurrency = $this->currencyRepository->findCurrencyByCode($transactionRecord->dividendCurrency);
				}

				$dividend = new Dividend(
					user: $user,
					asset: $asset,
					broker: $broker,
					paidDate: $transactionRecord->created ?? new DateTimeImmutable(),
					priceGross: $transactionRecord->dividendPrice ? (string) $transactionRecord->dividendPrice : '0',
					priceNet: $transactionRecord->dividendPrice ? (string) $transactionRecord->dividendPrice : '0',
					tax: '0',
					currency: $dividendCurrency ?? $defaultCurrency,
					exchangeRate: (string) ((new Decimal(1))->div($transactionRecord->exchangeRate ?? 1)),
				);
				$this->dividendRepository->persist($dividend);

				if ($firstDate === null || $dividend->getPaidDate()->getTimestamp() < $firstDate->getTimestamp()) {
					$firstDate = $dividend->getPaidDate();
				}

				continue;
			}

			$actionType = TransactionActionTypeEnum::Undefined;
			if (strpos($transactionRecord->actionType ?? '', 'buy') !== false) {
				$actionType = TransactionActionTypeEnum::Buy;
			} elseif (strpos($transactionRecord->actionType ?? '', 'sell') !== false) {
				$actionType = TransactionActionTypeEnum::Sell;
			}

			$units = $transactionRecord->units ?? new Decimal(0);
			if ($actionType === TransactionActionTypeEnum::Sell) {
				$units = $units->negate();
			}

			$transaction = new Transaction(
				user: $user,
				asset: $asset,
				broker: $broker,
				actionType: $actionType->value,
				created: $transactionRecord->created ?? new DateTimeImmutable(),
				units: (string) $units,
				priceUnit: $transactionRecord->priceUnit ? (string) $transactionRecord->priceUnit : '0',
				currency: $currency,
				feeConversion: $transactionRecord->feeConversion ? (string) $transactionRecord->feeConversion : '0',
				notes: $transactionRecord->notes,
				importIdentifier: $transactionRecord->importIdentifier,
			);
			$this->transactionRepository->persist($transaction);

			if ($firstDate === null || $transaction->getCreated()->getTimestamp() < $firstDate->getTimestamp()) {
				$firstDate = $transaction->getCreated();
			}
		}

		if ($firstDate === null) {
			return;
		}

		$this->portfolioDataRepository->deletePortfolioData($user->getId(), $firstDate);
		$this->groupDataRepository->deleteUserGroupData($user->getId(), $firstDate);
	}

	/** @param array<string, string> $csvRecord */
	private function mapTransactionRecord(MapperInterface $mapper, array $csvRecord): TransactionRecord
	{
		$mappedRecord = [];

		foreach ($mapper->getCsvMapping() as $attribute => $recordKey) {
			if (!is_string($recordKey)) {
				$mappedRecord[$attribute] = $recordKey($csvRecord);
				continue;
			}

			$mappedRecord[$attribute] = $csvRecord[$recordKey] ?? null;
		}

		$ticker = ($mappedRecord['ticker'] ?? '') !== '' ? ($mappedRecord['ticker'] ?? null) : null;

		return new TransactionRecord(
			ticker: $ticker,
			actionType: strtolower($mappedRecord['actionType'] ?? ''),
			created: new DateTimeImmutable($mappedRecord['created'] ?? ''),
			units: $mappedRecord['units'] ? new Decimal($mappedRecord['units']) : null,
			priceUnit: $mappedRecord['priceUnit'] ? new Decimal($mappedRecord['priceUnit']) : null,
			currency: $mappedRecord['currency'],
			feeConversion: $mappedRecord['feeConversion'] ? new Decimal($mappedRecord['feeConversion']) : null,
			notes: $mappedRecord['notes'] ?? null,
			importIdentifier: $mappedRecord['importIdentifier'] ?? null,
			dividendPrice: $mappedRecord['dividendPrice'] ? new Decimal($mappedRecord['dividendPrice']) : null,
			dividendCurrency: $mappedRecord['dividendCurrency'] ?? null,
		);
	}

	private function getImportMapper(BrokerImportTypeEnum $importType): MapperInterface
	{
		return match ($importType) {
			BrokerImportTypeEnum::Revolut => new RevolutMapper(),
			BrokerImportTypeEnum::Trading212 => new Trading212Mapper(),
			BrokerImportTypeEnum::Anycoin => new AnycoinMapper(),
		};
	}
}
