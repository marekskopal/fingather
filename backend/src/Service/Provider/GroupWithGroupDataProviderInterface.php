<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Dto\GroupWithGroupDataDto;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;

interface GroupWithGroupDataProviderInterface
{
	/** @return list<GroupWithGroupDataDto> */
	public function getGroupsWithGroupData(User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): array;
}
