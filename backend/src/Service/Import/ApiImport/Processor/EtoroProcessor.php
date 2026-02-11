<?php

declare(strict_types=1);

namespace FinGather\Service\Import\ApiImport\Processor;

use DateInterval;
use DateTimeImmutable;
use FinGather\Model\Entity\ApiImport;
use FinGather\Model\Entity\ApiKey;
use FinGather\Model\Entity\Enum\ApiImportStatusEnum;
use FinGather\Service\Import\ImportService;
use FinGather\Service\Provider\ApiImportProvider;
use FinGather\Service\Provider\ImportFileProvider;
use FinGather\Service\Provider\ImportProvider;
use League\Csv\Writer;
use MarekSkopal\Etoro\Config\Config;
use MarekSkopal\Etoro\Dto\MarketData\InstrumentMetadata;
use MarekSkopal\Etoro\Dto\Trading\Position;
use MarekSkopal\Etoro\Dto\Trading\TradeHistory;
use MarekSkopal\Etoro\Etoro;
use Ramsey\Uuid\Uuid;

final readonly class EtoroProcessor implements ProcessorInterface
{
	public function __construct(
		private ApiImportProvider $apiImportProvider,
		private ImportService $importService,
		private ImportProvider $importProvider,
		private ImportFileProvider $importFileProvider,
	) {
	}

	public function prepare(ApiKey $apiKey): void
	{
		$lastApiImport = $this->apiImportProvider->getLastApiImport($apiKey);

		$dateFrom = $lastApiImport?->dateTo->sub(new DateInterval('P1D')) ?? new DateTimeImmutable('2000-01-01');

		$dateTo = new DateTimeImmutable('today');

		$apiImport = $this->apiImportProvider->createApiImport(
			user: $apiKey->user,
			portfolio: $apiKey->portfolio,
			apiKey: $apiKey,
			dateFrom: $dateFrom,
			dateTo: $dateTo,
			reportId: null,
		);

		$this->apiImportProvider->updateApiImport($apiImport, ApiImportStatusEnum::Waiting);
	}

	public function process(ApiImport $apiImport): void
	{
		$this->apiImportProvider->updateApiImport($apiImport, ApiImportStatusEnum::InProgress);

		try {
			$apiKey = $apiImport->apiKey;
			assert($apiKey->userKey !== null);

			$etoro = new Etoro(new Config(apiKey: $apiKey->apiKey, userKey: $apiKey->userKey));

			$tradeHistory = $etoro->tradingReal->history($apiImport->dateFrom);
			$portfolio = $etoro->tradingReal->portfolio();

			$instrumentIds = $this->collectInstrumentIds($tradeHistory, $portfolio->positions);

			$instrumentMap = [];
			if (count($instrumentIds) > 0) {
				$instruments = $etoro->marketData->instrumentsMetadata(instrumentIds: $instrumentIds);
				foreach ($instruments as $instrument) {
					$instrumentMap[$instrument->instrumentID] = $instrument;
				}
			}

			$csvRows = $this->buildCsv($tradeHistory, $portfolio->positions, $instrumentMap);

			if (count($csvRows) === 0) {
				$this->apiImportProvider->updateApiImport($apiImport, ApiImportStatusEnum::Finished);
				return;
			}

			$csvContent = $this->createCsvContent($csvRows);

			$import = $this->importProvider->createImport($apiImport->user, $apiImport->portfolio, Uuid::uuid4());
			$this->importFileProvider->createImportFile($import, 'etoro_api.csv', $csvContent);

			$this->importService->importDataFiles($import);

			$this->apiImportProvider->updateApiImport($apiImport, ApiImportStatusEnum::Finished);
		} catch (\Throwable $e) {
			$this->apiImportProvider->updateApiImport(
				apiImport: $apiImport,
				status: ApiImportStatusEnum::Error,
				error: $e->getMessage(),
			);
		}
	}

	/**
	 * @param list<TradeHistory> $tradeHistory
	 * @param list<Position> $positions
	 * @return list<int>
	 */
	private function collectInstrumentIds(array $tradeHistory, array $positions): array
	{
		$ids = [];
		foreach ($tradeHistory as $trade) {
			$ids[$trade->instrumentId] = true;
		}
		foreach ($positions as $position) {
			$ids[$position->instrumentId] = true;
		}

		return array_map(intval(...), array_keys($ids));
	}

	/**
	 * @param list<TradeHistory> $tradeHistory
	 * @param list<Position> $positions
	 * @param array<int, InstrumentMetadata> $instrumentMap
	 * @return list<array<string, string>>
	 */
	private function buildCsv(array $tradeHistory, array $positions, array $instrumentMap): array
	{
		$rows = [];

		foreach ($tradeHistory as $trade) {
			$ticker = isset($instrumentMap[$trade->instrumentId]) ? $instrumentMap[$trade->instrumentId]->symbolFull : '';

			$rows[] = [
				'Action' => $trade->isBuy ? 'Buy' : 'Sell',
				'Date' => $trade->openTimestamp->format('Y-m-d H:i:s'),
				'Ticker' => $ticker,
				'Units' => (string) $trade->units,
				'Price' => (string) ($trade->isBuy ? $trade->openRate : $trade->closeRate),
				'Currency' => 'USD',
				'Fee' => '0',
				'FeeCurrency' => 'USD',
				'IsAdjusted' => '1',
				'ImportIdentifier' => (string) $trade->positionId,
			];
		}

		foreach ($positions as $position) {
			$ticker = isset($instrumentMap[$position->instrumentId])
				? $instrumentMap[$position->instrumentId]->symbolFull
				: (string) $position->instrumentId;

			$rows[] = [
				'Action' => $position->isBuy ? 'Buy' : 'Sell',
				'Date' => $position->openDateTime->format('Y-m-d H:i:s'),
				'Ticker' => $ticker,
				'Units' => (string) $position->units,
				'Price' => (string) ($position->isBuy ? $position->openRate : $position->closeRate),
				'Currency' => 'USD',
				'Fee' => (string) abs($position->totalFees ?? 0),
				'FeeCurrency' => 'USD',
				'IsAdjusted' => '1',
				'ImportIdentifier' => (string) $position->positionId,
			];
		}

		return $rows;
	}

	/** @param list<array<string, string>> $rows */
	private function createCsvContent(array $rows): string
	{
		$header = ['Action', 'Date', 'Ticker', 'Units', 'Price', 'Currency', 'Fee', 'FeeCurrency', 'IsAdjusted', 'ImportIdentifier'];

		$csv = Writer::fromString();
		$csv->insertOne($header);
		$csv->insertAll($rows);

		return $csv->toString();
	}
}
