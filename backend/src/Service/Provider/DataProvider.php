<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\User;
use Safe\DateTimeImmutable;

class DataProvider
{
	public function __construct(
		private readonly GroupDataProvider $groupDataProvider,
		private readonly PortfolioDataProvider $portfolioDataProvider,
	) {
	}

	public function deleteUserData(User $user, DateTimeImmutable $date): void
	{
		$this->groupDataProvider->deleteUserGroupData($user, $date);
		$this->portfolioDataProvider->deletePortfolioData($user, $date);
	}
}
