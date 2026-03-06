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
use FinGather\Service\DataCalculator\Dto\ReturnRateDto;
use Iterator;

interface DcaPlanProviderInterface
{
	/** @return Iterator<DcaPlan> */
	public function getDcaPlans(User $user, Portfolio $portfolio): Iterator;

	public function getDcaPlan(int $dcaPlanId, User $user): ?DcaPlan;

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
	): DcaPlan;

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
	): DcaPlan;

	public function deleteDcaPlan(DcaPlan $dcaPlan): void;

	public function getReturnRate(DcaPlan $dcaPlan): ReturnRateDto;

	public function getProjection(DcaPlan $dcaPlan, int $horizonYears = 10, bool $withCurrentValue = true): DcaPlanProjectionDto;
}
