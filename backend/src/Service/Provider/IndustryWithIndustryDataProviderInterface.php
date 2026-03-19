<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Dto\IndustryWithIndustryDataDto;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;

interface IndustryWithIndustryDataProviderInterface
{
	/** @return list<IndustryWithIndustryDataDto> */
	public function getIndustriesWithIndustryData(User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): array;
}
