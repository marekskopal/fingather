<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Sector;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<Sector> */
final class SectorRepository extends AbstractRepository
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
