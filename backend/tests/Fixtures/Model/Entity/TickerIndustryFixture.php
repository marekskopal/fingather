<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use FinGather\Model\Entity\TickerIndustry;

final class TickerIndustryFixture
{
	public static function getTickerIndustry(?string $name = null, ?bool $isOthers = null): TickerIndustry
	{
		return new TickerIndustry(name: $name ?? 'Consumer Electronics', isOthers: $isOthers ?? false);
	}
}
