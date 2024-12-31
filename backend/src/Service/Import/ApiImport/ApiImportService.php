<?php

declare(strict_types=1);

namespace FinGather\Service\Import\ApiImport;

use FinGather\Dto\ApiImportPrepareCheckDto;
use FinGather\Dto\ApiImportProcessCheckDto;
use FinGather\Service\Import\ApiImport\Factory\ProcessorFactory;
use FinGather\Service\Provider\ApiImportProvider;
use FinGather\Service\Provider\ApiKeyProvider;
use Psr\Log\LoggerInterface;

class ApiImportService
{
	public function __construct(
		private readonly ProcessorFactory $processorFactory,
		private readonly ApiKeyProvider $apiKeyProvider,
		private readonly ApiImportProvider $apiImportProvider,
		private readonly LoggerInterface $logger,
	) {
	}

	public function prepareImport(ApiImportPrepareCheckDto $apiImportPrepareCheck): void
	{
		$apiKey = $this->apiKeyProvider->getApiKey(apiKeyId: $apiImportPrepareCheck->apiKeyId);
		if ($apiKey === null) {
			$this->logger->error('Preparing API import - ApiKey not found - apiKeyId:' . $apiImportPrepareCheck->apiKeyId);
			return;
		}

		$this->logger->info('Preparing API import - apiKeyId:' . $apiKey->id);

		$processor = $this->processorFactory->create($apiKey->type);
		$processor->prepare($apiKey);
	}

	public function processImport(ApiImportProcessCheckDto $apiImportProcessCheck): void
	{
		$apiImport = $this->apiImportProvider->getApiImport($apiImportProcessCheck->apiImportId);
		if ($apiImport === null) {
			$this->logger->error('Processing API import - ApiImport not found - apiImportId:' . $apiImportProcessCheck->apiImportId);
			return;
		}

		$this->logger->info('Processing API import - apiImportId:' . $apiImport->id);

		$processor = $this->processorFactory->create($apiImport->apiKey->type);
		$processor->process($apiImport);
	}
}
