<?php

declare(strict_types=1);

namespace FinGather\Helper;

use ArrayIterator;
use DateTimeImmutable;
use Iterator;

/** @extends \DatePeriod<DateTimeImmutable, DateTimeImmutable, int|null> */
final class DatePeriod extends \DatePeriod
{
	public function getIterator(): Iterator
	{
		return new ArrayIterator(array_merge(
			iterator_to_array(parent::getIterator(), false),
			[parent::getEndDate()],
		));
	}
}
