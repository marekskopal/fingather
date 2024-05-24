<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use DateTimeImmutable;
use FinGather\Model\Entity\CountryData;

/** @extends ARepository<CountryData> */
final class CountryDataRepository extends ARepository
{
	public function findCountryData(int $countryId, int $portfolioId, DateTimeImmutable $date): ?CountryData
	{
		return $this->findOne([
			'country_id' => $countryId,
			'portfolio_id' => $portfolioId,
			'date' => $date,
		]);
	}

	public function deleteCountryData(int $countryId, ?DateTimeImmutable $date = null): void
	{
		$deleteGroupData = $this->orm->getSource(CountryData::class)
			->getDatabase()
			->delete('country_datas')
			->where('country_id', $countryId);

		if ($date !== null) {
			$deleteGroupData->where('date', '>=', $date);
		}

		$deleteGroupData->run();
	}

	public function deleteUserCountryData(?int $userId = null, ?int $portfolioId = null, ?DateTimeImmutable $date = null): void
	{
		$deleteUserGroupData = $this->orm->getSource(CountryData::class)
			->getDatabase()
			->delete('country_datas');

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
