<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Sector;
use FinGather\Model\Entity\User;

interface SectorProviderInterface
{
	/** @return array<int, Sector> */
	public function getSectorsFromAssets(User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): array;
}
