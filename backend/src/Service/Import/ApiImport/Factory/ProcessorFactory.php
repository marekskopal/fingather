<?php

declare(strict_types=1);

namespace FinGather\Service\Import\ApiImport\Factory;

use FinGather\Model\Entity\Enum\ApiKeyTypeEnum;
use FinGather\Service\Import\ApiImport\Processor\EtoroProcessor;
use FinGather\Service\Import\ApiImport\Processor\ProcessorInterface;
use FinGather\Service\Import\ApiImport\Processor\Trading212Processor;
use FinGather\Service\Import\ImportService;
use FinGather\Service\Provider\ApiImportProviderInterface;
use FinGather\Service\Provider\ImportFileProviderInterface;
use FinGather\Service\Provider\ImportProviderInterface;

class ProcessorFactory
{
	public function __construct(
		private readonly ApiImportProviderInterface $apiImportProvider,
		private readonly ImportService $importService,
		private readonly ImportProviderInterface $importProvider,
		private readonly ImportFileProviderInterface $importFileProvider,
	) {
	}

	public function create(ApiKeyTypeEnum $type): ProcessorInterface
	{
		return match ($type) {
			ApiKeyTypeEnum::Trading212 => new Trading212Processor(
				apiImportProvider: $this->apiImportProvider,
				importService: $this->importService,
				importProvider: $this->importProvider,
				importFileProvider: $this->importFileProvider,
			),
			ApiKeyTypeEnum::Etoro => new EtoroProcessor(
				apiImportProvider: $this->apiImportProvider,
				importService: $this->importService,
				importProvider: $this->importProvider,
				importFileProvider: $this->importFileProvider,
			),
		};
	}
}
