<?php

declare(strict_types=1);

namespace FinGather\Service\Goal;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Goal;

interface GoalCheckerInterface
{
	public function getCurrentValue(Goal $goal, DateTimeImmutable $now): Decimal;

	public function isAchieved(Goal $goal, Decimal $currentValue): bool;

	public function getProgressPercentage(Goal $goal, Decimal $currentValue): float;
}
