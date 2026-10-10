<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\DcaPlan;
use FinGather\Model\Entity\Enum\GoalTypeEnum;
use FinGather\Model\Entity\Goal;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;

interface GoalProviderInterface
{
	/** @return list<Goal> */
	public function getGoals(User $user, Portfolio $portfolio): array;

	public function getGoal(int $goalId, User $user): ?Goal;

	/** @return list<Goal> */
	public function getActiveGoals(): array;

	public function createGoal(
		User $user,
		Portfolio $portfolio,
		GoalTypeEnum $type,
		Decimal $targetValue,
		?DateTimeImmutable $deadline,
		?DcaPlan $dcaPlan = null,
	): Goal;

	public function updateGoal(
		Goal $goal,
		Portfolio $portfolio,
		GoalTypeEnum $type,
		Decimal $targetValue,
		?DateTimeImmutable $deadline,
		bool $isActive,
		?DcaPlan $dcaPlan = null,
	): Goal;

	public function markAchieved(Goal $goal): void;

	public function deleteGoal(Goal $goal): void;
}
