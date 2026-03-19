<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;

interface GroupDataProviderInterface
{
	public function getGroupData(Group $group, User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): CalculatedDataDto;

	public function deleteUserGroupData(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $date = null): void;
}
