<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Sector;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;

interface SectorDataProviderInterface
{
	public function getSectorData(Sector $sector, User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): CalculatedDataDto;

	public function deleteUserSectorData(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $date = null): void;
}
