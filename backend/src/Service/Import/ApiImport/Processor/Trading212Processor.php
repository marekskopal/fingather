<?php

declare(strict_types=1);

namespace FinGather\Service\Import\ApiImport\Processor;

use DateInterval;
use FinGather\Model\Entity\ApiImport;
use FinGather\Model\Entity\ApiKey;
use FinGather\Model\Entity\Enum\ApiImportStatusEnum;
use FinGather\Service\Import\ImportService;
use FinGather\Service\Provider\ApiImportProvider;
use FinGather\Service\Provider\ImportFileProvider;
use FinGather\Service\Provider\ImportProvider;
use FinGather\Utils\DateTimeUtils;
use MarekSkopal\Trading212\Config\Config;
use MarekSkopal\Trading212\Dto\HistoricalItems\DataIncluded;
use MarekSkopal\Trading212\Dto\HistoricalItems\Export;
use MarekSkopal\Trading212\Dto\HistoricalItems\ExportCsv;
use MarekSkopal\Trading212\Trading212;
use Safe\DateTimeImmutable;
use Safe\Exceptions\FilesystemException;
use function Safe\file_get_contents;

class Trading212Processor implements ProcessorInterface
{
	public function __construct(
		private readonly ApiImportProvider $apiImportProvider,
		private readonly ImportService $importService,
		private readonly ImportProvider $importProvider,
		private readonly ImportFileProvider $importFileProvider,
	) {
	}

	public function prepare(ApiKey $apiKey): void
	{
		$trading212 = new Trading212(new Config($apiKey->getApiKey()));

		$lastApiImport = $this->apiImportProvider->getLastApiImport($apiKey);

		$dateFrom = $lastApiImport?->getDateFrom()->sub(new DateInterval('P1D')) ?? new DateTimeImmutable(DateTimeUtils::FirstDate);

		$dateTo = new DateTimeImmutable('today');

		$exportCsv = new ExportCsv(
			dataIncluded: new DataIncluded(
				includeDividends: true,
				includeInterest: false,
				includeOrders: false,
				includeTransactions: true,
			),
			timeFrom: $dateFrom,
			timeTo: $dateTo,
		);

		$report = $trading212->getHistoricalItems()->exportCsv($exportCsv);

		$apiImport = $this->apiImportProvider->createApiImport(
			user: $apiKey->getUser(),
			portfolio: $apiKey->getPortfolio(),
			apiKey: $apiKey,
			dateFrom: $dateFrom,
			dateTo: $dateTo,
			reportId: $report->reportId,
		);

		$this->apiImportProvider->updateApiImport($apiImport, ApiImportStatusEnum::Waiting);
	}

	public function process(ApiImport $apiImport): void
	{
		$this->apiImportProvider->updateApiImport($apiImport, ApiImportStatusEnum::InProgress);

		$trading212 = new Trading212(new Config($apiImport->getApiKey()->getApiKey()));

		$exports = $trading212->getHistoricalItems()->exports();
		/** @var Export|null $export */
		$export = array_filter($exports, fn($item) => $item->reportId === $apiImport->getReportId())[0] ?? null;
		if ($export === null) {
			$this->apiImportProvider->updateApiImport($apiImport, ApiImportStatusEnum::Error);
			return;
		}

		if ($export->status !== 'Ready') {
			$this->apiImportProvider->updateApiImport($apiImport, ApiImportStatusEnum::Waiting);
			return;
		}

		try {
			$csvFile = file_get_contents($export->downloadLink);
		} catch (FilesystemException) {
			$this->apiImportProvider->updateApiImport($apiImport, ApiImportStatusEnum::Error);
			return;
		}

		$import = $this->importProvider->createImport($apiImport->getUser(), $apiImport->getPortfolio());
		$this->importFileProvider->createImportFile($import, 'trading212.csv', $csvFile);

		$this->importService->importDataFiles($import);

		$this->apiImportProvider->updateApiImport($apiImport, ApiImportStatusEnum::Finished);
	}
}
