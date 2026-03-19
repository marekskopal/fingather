<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Country;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Industry;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Sector;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;

interface CalculatedGroupDataProviderInterface
{
	public function getCalculatedData(
		User $user,
		Portfolio $portfolio,
		DateTimeImmutable $dateTime,
		?Group $group = null,
		?Country $country = null,
		?Sector $sector = null,
		?Industry $industry = null,
	): CalculatedDataDto;
}
