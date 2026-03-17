<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Factory;

use FinGather\Service\Import\Entity\TransactionRecord;
use FinGather\Service\Import\Mapper\MapperInterface;

interface TransactionRecordFactoryInterface
{
	/** @param array<string, string> $csvRecord */
	public function createFromCsvRecord(MapperInterface $mapper, array $csvRecord): TransactionRecord;
}
