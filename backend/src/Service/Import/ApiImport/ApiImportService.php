<?php

declare(strict_types=1);

namespace FinGather\Service\Import\ApiImport;

use FinGather\Dto\ApiImportPrepareCheckDto;
use FinGather\Dto\ApiImportProcessCheckDto;
use FinGather\Model\Entity\Enum\ApiImportStatusEnum;
use FinGather\Service\Import\ApiImport\Factory\ProcessorFactory;
use FinGather\Service\Provider\ApiImportProvider;
use FinGather\Service\Provider\ApiKeyProvider;

class ApiImportService
{
	public function __construct(
		private readonly ProcessorFactory $processorFactory,
		private readonly ApiKeyProvider $apiKeyProvider,
		private readonly ApiImportProvider $apiImportProvider,
	) {
	}

	public function prepareImport(ApiImportPrepareCheckDto $apiImportPrepareCheck): void
	{
		$apiKey = $this->apiKeyProvider->getApiKey($apiImportPrepareCheck->apiKeyId);
		if ($apiKey === null) {
			return;
		}

		$processor = $this->processorFactory->create($apiKey->getType());
		$processor->prepare($apiKey);
	}

	public function processImport(ApiImportProcessCheckDto $apiImportProcessCheck): void
	{
		$apiImport = $this->apiImportProvider->getApiImport($apiImportProcessCheck->apiImportId);
		if ($apiImport === null) {
			return;
		}

		$this->apiImportProvider->updateApiImport($apiImport, ApiImportStatusEnum::InProgress);

		$processor = $this->processorFactory->create($apiImport->getApiKey()->getType());
		$processor->process($apiImport);
	}
}
