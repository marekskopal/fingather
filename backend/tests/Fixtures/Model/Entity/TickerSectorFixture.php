<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use FinGather\Model\Entity\Sector;

final class TickerSectorFixture
{
	public static function getTickerSector(?int $id = null, ?string $name = null, ?bool $isOthers = null): Sector
	{
		$sector = new Sector(name: $name ?? 'Technology', isOthers: $isOthers ?? false);
		$sector->id = $id ?? 1;
		return $sector;
	}
}
