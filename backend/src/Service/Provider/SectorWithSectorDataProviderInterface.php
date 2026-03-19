<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Dto\SectorWithSectorDataDto;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;

interface SectorWithSectorDataProviderInterface
{
	/** @return list<SectorWithSectorDataDto> */
	public function getSectorsWithSectorData(User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): array;
}
