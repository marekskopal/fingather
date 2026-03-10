<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\DcaPlan;
use FinGather\Model\Entity\Enum\DcaPlanTargetTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;

final class DcaPlanFixture
{
	/** @phpstan-ignore-next-line public.method.unused */
	public static function getDcaPlan(
		?int $id = null,
		?User $user = null,
		?DcaPlanTargetTypeEnum $targetType = null,
		?Portfolio $portfolio = null,
		?Currency $currency = null,
		?Decimal $amount = null,
		int $intervalMonths = 1,
		?DateTimeImmutable $startDate = null,
		?DateTimeImmutable $endDate = null,
	): DcaPlan {
		$dcaPlan = new DcaPlan(
			user: $user ?? UserFixture::getUser(),
			targetType: $targetType ?? DcaPlanTargetTypeEnum::Portfolio,
			portfolio: $portfolio ?? PortfolioFixture::getPortfolio(),
			asset: null,
			group: null,
			strategy: null,
			amount: $amount ?? new Decimal('500'),
			currency: $currency ?? CurrencyFixture::getCurrency(),
			intervalMonths: $intervalMonths,
			startDate: $startDate ?? new DateTimeImmutable('2024-01-01'),
			endDate: $endDate,
			createdAt: new DateTimeImmutable('2024-01-01'),
		);

		$dcaPlan->id = $id ?? 1;

		return $dcaPlan;
	}
}
