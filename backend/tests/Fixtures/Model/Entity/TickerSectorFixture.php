<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use FinGather\Model\Entity\Sector;

final class TickerSectorFixture
{
	public static function getTickerSector(?string $name = null, ?bool $isOthers = null): Sector
	{
		return new Sector(name: $name ?? 'Technology', isOthers: $isOthers ?? false);
	}
}
