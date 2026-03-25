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
use FinGather\Model\Repository\GoalRepository;
use Iterator;

final readonly class GoalProvider implements GoalProviderInterface
{
	public function __construct(private GoalRepository $goalRepository)
	{
	}

	/** @return Iterator<Goal> */
	public function getGoals(User $user, Portfolio $portfolio): Iterator
	{
		return $this->goalRepository->findGoals($user->id, $portfolio->id);
	}

	public function getGoal(int $goalId, User $user): ?Goal
	{
		return $this->goalRepository->findGoal($goalId, $user->id);
	}

	/** @return Iterator<Goal> */
	public function getActiveGoals(): Iterator
	{
		return $this->goalRepository->findActiveGoals();
	}

	public function createGoal(
		User $user,
		Portfolio $portfolio,
		GoalTypeEnum $type,
		Decimal $targetValue,
		?DateTimeImmutable $deadline,
		?DcaPlan $dcaPlan = null,
	): Goal {
		$goal = new Goal(
			user: $user,
			portfolio: $portfolio,
			type: $type,
			targetValue: $targetValue,
			deadline: $deadline,
			isActive: true,
			achievedAt: null,
			createdAt: new DateTimeImmutable(),
			dcaPlan: $dcaPlan,
		);
		$this->goalRepository->persist($goal);

		return $goal;
	}

	public function updateGoal(
		Goal $goal,
		Portfolio $portfolio,
		GoalTypeEnum $type,
		Decimal $targetValue,
		?DateTimeImmutable $deadline,
		bool $isActive,
		?DcaPlan $dcaPlan = null,
	): Goal {
		$goal->portfolio = $portfolio;
		$goal->type = $type;
		$goal->targetValue = $targetValue;
		$goal->deadline = $deadline;
		$goal->isActive = $isActive;
		$goal->dcaPlan = $dcaPlan;
		$this->goalRepository->persist($goal);

		return $goal;
	}

	public function markAchieved(Goal $goal): void
	{
		$goal->achievedAt = new DateTimeImmutable();
		$goal->isActive = false;
		$this->goalRepository->persist($goal);
	}

	public function deleteGoal(Goal $goal): void
	{
		$this->goalRepository->delete($goal);
	}
}
