<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Sector;

/** @extends ARepository<Sector> */
final class SectorRepository extends ARepository
{
	public function findSectorByName(string $name): ?Sector
	{
		return $this->findOne([
			'name' => $name,
		]);
	}

	public function findOthersSector(): Sector
	{
		$othersTickerSector = $this->findOne([
			'is_others' => true,
		]);
		assert($othersTickerSector instanceof Sector);
		return $othersTickerSector;
	}
}
