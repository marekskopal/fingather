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
use MarekSkopal\Trading212\Config\Config;
use MarekSkopal\Trading212\Dto\HistoricalItems\DataIncluded;
use MarekSkopal\Trading212\Dto\HistoricalItems\Export;
use MarekSkopal\Trading212\Dto\HistoricalItems\ExportCsv;
use MarekSkopal\Trading212\Trading212;
use Ramsey\Uuid\Uuid;
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

		if ($lastApiImport !== null) {
			$dateFrom = $lastApiImport->getDateTo()->sub(new DateInterval('P1D'));
		} else {
			$transactions = iterator_to_array($trading212->getHistoricalItems()->orders(
				limit: 50,
			)->fetchAll(), false);

			if (count($transactions) === 0) {
				return;
			}

			$firstTransaction = $transactions[array_key_last($transactions)];
			$dateFrom = $firstTransaction->dateCreated;
		}

		$dateTo = $dateFrom->add(new DateInterval('P354D'));
		$createNextImport = true;
		if ($dateTo > new DateTimeImmutable('today')) {
			$dateTo = new DateTimeImmutable('today');
			$createNextImport = false;
		}

		$exportCsv = new ExportCsv(
			dataIncluded: new DataIncluded(
				includeDividends: true,
				includeInterest: false,
				includeOrders: true,
				includeTransactions: false,
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

		if ($createNextImport) {
			$this->prepare($apiKey);
		}
	}

	public function process(ApiImport $apiImport): void
	{
		$this->apiImportProvider->updateApiImport($apiImport, ApiImportStatusEnum::InProgress);

		$trading212 = new Trading212(new Config($apiImport->getApiKey()->getApiKey()));

		$exports = $trading212->getHistoricalItems()->exports();
		$export = array_values(array_filter($exports, fn(Export $item): bool => $item->reportId === $apiImport->getReportId()))[0] ?? null;
		if ($export === null) {
			$this->apiImportProvider->updateApiImport(apiImport: $apiImport, status: ApiImportStatusEnum::Error, error: 'Export not found');
			return;
		}

		if ($export->status !== 'Finished') {
			$this->apiImportProvider->updateApiImport($apiImport, ApiImportStatusEnum::Waiting);
			return;
		}

		try {
			$downloadLink = $export->downloadLink;
			assert($downloadLink !== null);
			$csvFile = file_get_contents($downloadLink);
		} catch (FilesystemException $exception) {
			$this->apiImportProvider->updateApiImport(
				apiImport: $apiImport,
				status: ApiImportStatusEnum::Error,
				error: 'Error downloading file: ' . $exception->getMessage(),
			);
			return;
		}

		$import = $this->importProvider->createImport($apiImport->getUser(), $apiImport->getPortfolio(), Uuid::uuid4());
		$this->importFileProvider->createImportFile($import, 'trading212.csv', $csvFile);

		$this->importService->importDataFiles($import);

		$this->apiImportProvider->updateApiImport($apiImport, ApiImportStatusEnum::Finished);
	}
}
