<?php

declare(strict_types=1);

namespace FinGather\Service\Import\ApiImport\Factory;

use FinGather\Model\Entity\Enum\ApiKeyTypeEnum;
use FinGather\Service\Import\ApiImport\Processor\ProcessorInterface;

interface ProcessorFactoryInterface
{
	public function create(ApiKeyTypeEnum $type): ProcessorInterface;
}
