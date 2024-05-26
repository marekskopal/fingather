<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use DateTimeImmutable;
use FinGather\Model\Entity\CountryData;
use FinGather\Model\Entity\IndustryData;

/** @extends ARepository<IndustryData> */
final class IndustryDataRepository extends ARepository
{
	public function findIndustryData(int $industryId, int $portfolioId, DateTimeImmutable $date): ?IndustryData
	{
		return $this->findOne([
			'industry_id' => $industryId,
			'portfolio_id' => $portfolioId,
			'date' => $date,
		]);
	}

	public function deleteUserIndustryData(?int $userId = null, ?int $portfolioId = null, ?DateTimeImmutable $date = null): void
	{
		$deleteUserGroupData = $this->orm->getSource(CountryData::class)
			->getDatabase()
			->delete('industry_datas');

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
