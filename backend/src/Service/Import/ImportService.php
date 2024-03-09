<?php

declare(strict_types=1);

namespace FinGather\Service\Import;

use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Broker;
use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Enum\TransactionCreateTypeEnum;
use FinGather\Model\Entity\Import;
use FinGather\Model\Entity\Ticker;
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
use FinGather\Service\Provider\DataProvider;
use FinGather\Service\Provider\GroupProvider;
use FinGather\Service\Provider\ImportMappingProvider;
use FinGather\Service\Provider\ImportProvider;
use FinGather\Service\Provider\TickerProvider;
use FinGather\Service\Provider\TransactionProvider;
use FinGather\Utils\Base64Utils;
use Psr\Log\LoggerInterface;
use Safe\DateTimeImmutable;
use function Safe\json_decode;
use function Safe\json_encode;

final class ImportService
{
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
		private readonly LoggerInterface $logger,
	) {
	}

	/** @param array<string> $contents */
	public function prepareImport(Broker $broker, array $contents): PrepareImport
	{
		$importMapper = $this->getImportMapper($broker->getImportType());

		$user = $broker->getUser();
		$portfolio = $broker->getPortfolio();

		$importMappings = $this->importMappingProvider->getImportMappings($user, $portfolio, $broker);

		$notFoundTickers = [];
		$multipleFoundTickers = [];
		$okFoundTickers = [];

		foreach ($contents as $content) {
			foreach ($importMapper->getRecords($content) as $record) {
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
						ticker: $transactionRecord->ticker,
						tickers: $this->tickerProvider->getTickersByTicker($transactionRecord->ticker),
					);
				} else {
					$tickerByTicker = $this->tickerProvider->getTickerByTicker($transactionRecord->ticker);
					assert($tickerByTicker instanceof Ticker);
					$okFoundTickers[$transactionRecord->ticker] = new PrepareImportTicker(
						ticker: $transactionRecord->ticker,
						tickers: [$tickerByTicker],
					);
				}
			}
		}

		$import = $this->importProvider->createImport(
			user: $user,
			portfolio: $portfolio,
			broker: $broker,
			csvContent: json_encode(Base64Utils::encodeList($contents)),
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
		$broker = $import->getBroker();

		$importMapper = $this->getImportMapper($broker->getImportType());

		$user = $import->getUser();
		$portfolio = $import->getPortfolio();
		$othersGroup = $this->groupProvider->getOthersGroup($user, $portfolio);
		$defaultCurrency = $user->getDefaultCurrency();

		$firstDate = null;

		$importMappings = $this->importMappingProvider->getImportMappings($user, $portfolio, $broker);

		/** @var list<string> $jsonContents */
		$jsonContents = json_decode($import->getCsvContent(), assoc: true);
		$contents = Base64Utils::decodeList($jsonContents);
		foreach ($contents as $content) {
			foreach ($importMapper->getRecords($content) as $record) {
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
			importIdentifier: $mappedRecord['importIdentifier'] ?? null,
		);
	}

	private function getImportMapper(BrokerImportTypeEnum $importType): MapperInterface
	{
		return match ($importType) {
			BrokerImportTypeEnum::Trading212 => new Trading212Mapper(),
			BrokerImportTypeEnum::InteractiveBrokers => new InteractiveBrokersMapper(),
			BrokerImportTypeEnum::Xtb => new XtbMapper(),
			BrokerImportTypeEnum::Etoro => new EtoroMapper(),
			BrokerImportTypeEnum::Revolut => new RevolutMapper(),
			BrokerImportTypeEnum::Anycoin => new AnycoinMapper(),
			//default => new NullMapper(),
		};
	}
}
