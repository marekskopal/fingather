<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Enum\GoalTypeEnum;
use FinGather\Model\Entity\Goal;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use Iterator;

interface GoalProviderInterface
{
	/** @return Iterator<Goal> */
	public function getGoals(User $user, Portfolio $portfolio): Iterator;

	public function getGoal(int $goalId, User $user): ?Goal;

	/** @return Iterator<Goal> */
	public function getActiveGoals(): Iterator;

	public function createGoal(
		User $user,
		Portfolio $portfolio,
		GoalTypeEnum $type,
		Decimal $targetValue,
		?DateTimeImmutable $deadline,
	): Goal;

	public function updateGoal(
		Goal $goal,
		Portfolio $portfolio,
		GoalTypeEnum $type,
		Decimal $targetValue,
		?DateTimeImmutable $deadline,
		bool $isActive,
	): Goal;

	public function markAchieved(Goal $goal): void;

	public function deleteGoal(Goal $goal): void;
}
