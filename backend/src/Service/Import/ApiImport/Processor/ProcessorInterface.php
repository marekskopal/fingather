<?php

declare(strict_types=1);

namespace FinGather\Service\Import\ApiImport\Processor;

use FinGather\Model\Entity\ApiImport;
use FinGather\Model\Entity\ApiKey;

interface ProcessorInterface
{
	public function prepare(ApiKey $apiKey): void;

	public function process(ApiImport $apiImport): void;
}
