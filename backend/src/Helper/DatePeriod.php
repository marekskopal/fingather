<?php

declare(strict_types=1);

namespace FinGather\Helper;

use ArrayIterator;
use Iterator;

class DatePeriod extends \DatePeriod
{
	public function getIterator(): Iterator
	{
		return new ArrayIterator(array_merge(
			iterator_to_array(parent::getIterator()),
			[parent::getEndDate()],
		));
	}
}
