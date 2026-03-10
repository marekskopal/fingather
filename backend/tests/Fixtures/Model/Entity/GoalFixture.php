<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Enum\GoalTypeEnum;
use FinGather\Model\Entity\Goal;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;

final class GoalFixture
{
	/** @phpstan-ignore-next-line public.method.unused */
	public static function getGoal(
		?int $id = null,
		?User $user = null,
		?Portfolio $portfolio = null,
		?GoalTypeEnum $type = null,
		?Decimal $targetValue = null,
		?DateTimeImmutable $deadline = null,
		bool $isActive = true,
	): Goal {
		$goal = new Goal(
			user: $user ?? UserFixture::getUser(),
			portfolio: $portfolio ?? PortfolioFixture::getPortfolio(),
			type: $type ?? GoalTypeEnum::PortfolioValue,
			targetValue: $targetValue ?? new Decimal('10000'),
			deadline: $deadline,
			isActive: $isActive,
			achievedAt: null,
			createdAt: new DateTimeImmutable('2024-01-01'),
		);

		$goal->id = $id ?? 1;

		return $goal;
	}
}
