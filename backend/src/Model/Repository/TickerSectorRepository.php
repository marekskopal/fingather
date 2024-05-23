<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\TickerSector;

/** @extends ARepository<TickerSector> */
final class TickerSectorRepository extends ARepository
{
	public function findTickerSectorByName(string $name): ?TickerSector
	{
		return $this->findOne([
			'name' => $name,
		]);
	}
}
