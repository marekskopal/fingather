<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Country;

/** @extends ARepository<Country> */
final class CountryRepository extends ARepository
{
	public function findCountryByIsoCode(string $name): ?Country
	{
		return $this->findOne([
			'iso_code' => $name,
		]);
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
