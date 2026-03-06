<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Enum\AlertConditionEnum;
use FinGather\Model\Entity\Enum\AlertRecurrenceEnum;
use FinGather\Model\Entity\Enum\PriceAlertTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\PriceAlert;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\User;

final class PriceAlertFixture
{
	public static function getPriceAlert(
		?int $id = null,
		?User $user = null,
		?Portfolio $portfolio = null,
		?Ticker $ticker = null,
		?PriceAlertTypeEnum $type = null,
		?AlertConditionEnum $condition = null,
		?Decimal $targetValue = null,
		?AlertRecurrenceEnum $recurrence = null,
		int $cooldownHours = 24,
		bool $isActive = true,
	): PriceAlert {
		$priceAlert = new PriceAlert(
			user: $user ?? UserFixture::getUser(),
			portfolio: $portfolio,
			ticker: $ticker ?? TickerFixture::getTicker(),
			type: $type ?? PriceAlertTypeEnum::Price,
			condition: $condition ?? AlertConditionEnum::Above,
			targetValue: $targetValue ?? new Decimal('200'),
			recurrence: $recurrence ?? AlertRecurrenceEnum::OneTime,
			cooldownHours: $cooldownHours,
			isActive: $isActive,
			lastTriggeredAt: null,
			createdAt: new DateTimeImmutable('2024-01-01'),
		);

		$priceAlert->id = $id ?? 1;

		return $priceAlert;
	}
}
