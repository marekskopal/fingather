<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use FinGather\Model\Entity\Industry;

final class TickerIndustryFixture
{
	public static function getTickerIndustry(?string $name = null, ?bool $isOthers = null): Industry
	{
		return new Industry(name: $name ?? 'Consumer Electronics', isOthers: $isOthers ?? false);
	}
}
