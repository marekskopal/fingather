<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Factory;

use FinGather\Service\Import\Mapper\MapperInterface;

interface ImportMapperFactoryInterface
{
	public function createImportMapper(string $fileName, string $contents): MapperInterface;
}
