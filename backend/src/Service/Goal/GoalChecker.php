<?php

declare(strict_types=1);

namespace FinGather\Service\Goal;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Enum\GoalTypeEnum;
use FinGather\Model\Entity\Goal;
use FinGather\Service\Provider\PortfolioDataProvider;

final readonly class GoalChecker
{
	public function __construct(private PortfolioDataProvider $portfolioDataProvider)
	{
	}

	public function getCurrentValue(Goal $goal, DateTimeImmutable $now): Decimal
	{
		$portfolioData = $this->portfolioDataProvider->getPortfolioData($goal->user, $goal->portfolio, $now);

		return match ($goal->type) {
			GoalTypeEnum::PortfolioValue => $portfolioData->value,
			GoalTypeEnum::ReturnPercentage => new Decimal((string) $portfolioData->returnPercentage),
			GoalTypeEnum::InvestedAmount => $portfolioData->transactionValue,
		};
	}

	public function isAchieved(Goal $goal, Decimal $currentValue): bool
	{
		return $currentValue->compareTo($goal->targetValue) >= 0;
	}

	public function getProgressPercentage(Goal $goal, Decimal $currentValue): float
	{
		if ($goal->targetValue->isZero()) {
			return 100.0;
		}

		$percentage = $currentValue->div($goal->targetValue)->mul(new Decimal('100'));

		return min(100.0, (float) $percentage->toString());
	}
}
