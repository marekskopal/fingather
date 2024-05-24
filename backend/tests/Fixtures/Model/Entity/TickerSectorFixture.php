<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use FinGather\Model\Entity\TickerSector;

final class TickerSectorFixture
{
	public static function getTickerSector(?string $name = null, ?bool $isOthers = null): TickerSector
	{
		return new TickerSector(name: $name ?? 'Technology', isOthers: $isOthers ?? false);
	}
}
