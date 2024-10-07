<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use DateTimeInterface;

interface BulkQueryEntityInterface
{
	/** @return list<string> */
	public function getBulkInsertColumns(): array;

	/** @return list<string|int|float|DateTimeInterface|null> */
	public function getBulkInsertValues(): array;
}
