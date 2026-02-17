<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Dto\CountryWithCountryDataDto;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;

interface CountryWithCountryDataProviderInterface
{
	/** @return list<CountryWithCountryDataDto> */
	public function getCountriesWithCountryData(User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): array;
}
