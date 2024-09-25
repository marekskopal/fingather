<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Country;

/** @extends ARepository<Country> */
final class CountryRepository extends ARepository
{
	public function findCountryByName(string $name): ?Country
	{
		$countrySelect = $this->select();
		$countrySelect->where('name', 'like', $name . '%');
		return $countrySelect->fetchOne();
	}

	public function findOthersCountry(): Country
	{
		$othersCountry = $this->findOne([
			'is_others' => true,
		]);
		assert($othersCountry instanceof Country);
		return $othersCountry;
	}
}
