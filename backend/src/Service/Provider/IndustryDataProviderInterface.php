<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Industry;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;

interface IndustryDataProviderInterface
{
	public function getIndustryData(Industry $industry, User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): CalculatedDataDto;

	public function deleteUserIndustryData(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $date = null): void;
}
