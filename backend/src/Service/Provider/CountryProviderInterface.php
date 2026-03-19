<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Country;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;

interface CountryProviderInterface
{
	/** @return array<int, Country> */
	public function getCountriesFromAssets(User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): array;
}
