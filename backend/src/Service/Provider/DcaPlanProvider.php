<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Dto\DcaPlanProjectionDto;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\DcaPlan;
use FinGather\Model\Entity\Enum\DcaPlanTargetTypeEnum;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Strategy;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\DcaPlanRepository;
use FinGather\Service\DataCalculator\DcaPlanDataCalculator;
use FinGather\Service\DataCalculator\Dto\ReturnRateDto;
use Iterator;

final readonly class DcaPlanProvider
{
	public function __construct(private DcaPlanRepository $dcaPlanRepository, private DcaPlanDataCalculator $dcaPlanDataCalculator,)
	{
	}

	/** @return Iterator<DcaPlan> */
	public function getDcaPlans(User $user, Portfolio $portfolio): Iterator
	{
		return $this->dcaPlanRepository->findDcaPlans($user->id, $portfolio->id);
	}

	public function getDcaPlan(int $dcaPlanId, User $user): ?DcaPlan
	{
		return $this->dcaPlanRepository->findDcaPlan($dcaPlanId, $user->id);
	}

	public function createDcaPlan(
		User $user,
		DcaPlanTargetTypeEnum $targetType,
		Portfolio $portfolio,
		?Asset $asset,
		?Group $group,
		?Strategy $strategy,
		Decimal $amount,
		Currency $currency,
		int $intervalMonths,
		DateTimeImmutable $startDate,
		?DateTimeImmutable $endDate,
	): DcaPlan {
		$dcaPlan = new DcaPlan(
			user: $user,
			targetType: $targetType,
			portfolio: $portfolio,
			asset: $asset,
			group: $group,
			strategy: $strategy,
			amount: $amount,
			currency: $currency,
			intervalMonths: $intervalMonths,
			startDate: $startDate,
			endDate: $endDate,
			createdAt: new DateTimeImmutable(),
		);
		$this->dcaPlanRepository->persist($dcaPlan);

		return $dcaPlan;
	}

	public function updateDcaPlan(
		DcaPlan $dcaPlan,
		DcaPlanTargetTypeEnum $targetType,
		Portfolio $portfolio,
		?Asset $asset,
		?Group $group,
		?Strategy $strategy,
		Decimal $amount,
		Currency $currency,
		int $intervalMonths,
		DateTimeImmutable $startDate,
		?DateTimeImmutable $endDate,
	): DcaPlan {
		$dcaPlan->targetType = $targetType;
		$dcaPlan->portfolio = $portfolio;
		$dcaPlan->asset = $asset;
		$dcaPlan->group = $group;
		$dcaPlan->strategy = $strategy;
		$dcaPlan->amount = $amount;
		$dcaPlan->currency = $currency;
		$dcaPlan->intervalMonths = $intervalMonths;
		$dcaPlan->startDate = $startDate;
		$dcaPlan->endDate = $endDate;
		$this->dcaPlanRepository->persist($dcaPlan);

		return $dcaPlan;
	}

	public function deleteDcaPlan(DcaPlan $dcaPlan): void
	{
		$this->dcaPlanRepository->delete($dcaPlan);
	}

	public function getReturnRate(DcaPlan $dcaPlan): ReturnRateDto
	{
		return $this->dcaPlanDataCalculator->calculateReturnRate($dcaPlan);
	}

	public function getProjection(DcaPlan $dcaPlan, int $horizonYears = 10, bool $withCurrentValue = true): DcaPlanProjectionDto
	{
		return $this->dcaPlanDataCalculator->getProjection($dcaPlan, $horizonYears, $withCurrentValue);
	}
}
