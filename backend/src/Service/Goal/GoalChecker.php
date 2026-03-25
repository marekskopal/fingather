<?php

declare(strict_types=1);

namespace FinGather\Service\Goal;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Dto\GoalReachabilityDto;
use FinGather\Model\Entity\Enum\GoalTypeEnum;
use FinGather\Model\Entity\Goal;
use FinGather\Service\DataCalculator\DcaPlanDataCalculator;
use FinGather\Service\Provider\PortfolioDataProviderInterface;

final readonly class GoalChecker implements GoalCheckerInterface
{
	private const int ProjectionHorizonYears = 50;

	public function __construct(
		private PortfolioDataProviderInterface $portfolioDataProvider,
		private DcaPlanDataCalculator $dcaPlanDataCalculator,
	) {
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

	public function getReachability(Goal $goal): GoalReachabilityDto
	{
		if ($goal->dcaPlan === null || $goal->type === GoalTypeEnum::ReturnPercentage) {
			return new GoalReachabilityDto(isReachable: null, projectedAchievementDate: null);
		}

		$deadlineYm = $goal->deadline?->format('Y-m');

		$projection = $this->dcaPlanDataCalculator->getProjection($goal->dcaPlan, self::ProjectionHorizonYears, withCurrentValue: true);

		$targetValueFloat = $goal->targetValue->toFloat();

		foreach ($projection->dataPoints as $point) {
			if ($deadlineYm !== null && $point->date > $deadlineYm) {
				break;
			}

			$compareValue = $goal->type === GoalTypeEnum::InvestedAmount
				? $point->investedCapital->toFloat()
				: $point->projectedValue->toFloat();

			if ($compareValue >= $targetValueFloat) {
				return new GoalReachabilityDto(isReachable: true, projectedAchievementDate: $point->date);
			}
		}

		return new GoalReachabilityDto(isReachable: false, projectedAchievementDate: null);
	}
}
