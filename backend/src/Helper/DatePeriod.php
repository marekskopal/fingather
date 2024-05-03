<?php

declare(strict_types=1);

namespace FinGather\Helper;

use ArrayIterator;
use DateTimeImmutable;
use Iterator;

/**
 * @method DateTimeImmutable current()
 * @method DateTimeImmutable getStartDate()
 * @method DateTimeImmutable getEndDate()
 */
final class DatePeriod extends \DatePeriod
{
	public function getIterator(): Iterator
	{
		return new ArrayIterator(array_merge(
			iterator_to_array(parent::getIterator()),
			[parent::getEndDate()],
		));
	}
}
