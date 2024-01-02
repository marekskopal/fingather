<?php

declare(strict_types=1);

namespace FinGather\Service\Import;

use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Broker;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Enum\TransactionCreateTypeEnum;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Repository\AssetRepository;
use FinGather\Model\Repository\CurrencyRepository;
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

			$actionType = TransactionActionTypeEnum::fromString($transactionRecord->actionType ?? '');

			$units = $transactionRecord->units ?? new Decimal(0);
			if ($actionType === TransactionActionTypeEnum::Sell) {
				$units = $units->negate();
			}

			$created = new DateTimeImmutable();

			$transaction = new Transaction(
				user: $user,
				asset: $asset,
				broker: $broker,
				actionType: $actionType->value,
				actionCreated: $transactionRecord->created ?? new DateTimeImmutable(),
				createType: TransactionCreateTypeEnum::Import->value,
				created: $created,
				modified: $created,
				units: (string) $units,
				price: $transactionRecord->price !== null ? (string) $transactionRecord->price : '0',
				currency: $currency,
				tax: $transactionRecord->tax !== null ? (string) $transactionRecord->tax : '0',
				notes: $transactionRecord->notes,
				importIdentifier: $transactionRecord->importIdentifier,
			);
			$this->transactionRepository->persist($transaction);

			if ($firstDate === null || $transaction->getActionCreated()->getTimestamp() < $firstDate->getTimestamp()) {
				$firstDate = $transaction->getActionCreated();
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
			price: $mappedRecord['price'] ? new Decimal($mappedRecord['price']) : null,
			currency: $mappedRecord['currency'],
			tax: $mappedRecord['tax'] ? new Decimal($mappedRecord['tax']) : null,
			notes: $mappedRecord['notes'] ?? null,
			importIdentifier: $mappedRecord['importIdentifier'] ?? null,
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
