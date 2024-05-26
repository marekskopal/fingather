<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use DateTimeImmutable;
use FinGather\Model\Entity\CountryData;
use FinGather\Model\Entity\SectorData;

/** @extends ARepository<SectorData> */
final class SectorDataRepository extends ARepository
{
	public function findSectorData(int $sectorId, int $portfolioId, DateTimeImmutable $date): ?SectorData
	{
		return $this->findOne([
			'sector_id' => $sectorId,
			'portfolio_id' => $portfolioId,
			'date' => $date,
		]);
	}

	public function deleteUserSectorData(?int $userId = null, ?int $portfolioId = null, ?DateTimeImmutable $date = null): void
	{
		$deleteUserGroupData = $this->orm->getSource(CountryData::class)
			->getDatabase()
			->delete('sector_datas');

		if ($userId !== null) {
			$deleteUserGroupData->where('user_id', $userId);
		}

		if ($portfolioId !== null) {
			$deleteUserGroupData->where('portfolio_id', $portfolioId);
		}

		if ($date !== null) {
			$deleteUserGroupData->where('date', '>=', $date);
		}

		$deleteUserGroupData->run();
	}
}
