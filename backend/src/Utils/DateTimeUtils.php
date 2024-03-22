<?php

declare(strict_types=1);

namespace FinGather\Utils;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use FinGather\Dto\Enum\RangeEnum;
use FinGather\Helper\DatePeriod;

final class DateTimeUtils
{
	public const FirstDate = '2000-01-01';

	private const FormatZulu = 'Y-m-d\TH:i:sp';

	public static function formatZulu(DateTimeImmutable|DateTime $dateTime): string
	{
		return $dateTime->format(self::FormatZulu);
	}

	public static function getDatePeriod(RangeEnum $range, ?DateTimeImmutable $firstDate = null): DatePeriod
	{
		return match ($range) {
			RangeEnum::SevenDays => new DatePeriod(
				// @phpstan-ignore-next-line
				new DateTimeImmutable('-1 week'),
				new DateInterval('P1D'),
				// @phpstan-ignore-next-line
				new DateTimeImmutable('today'),
			),
			RangeEnum::OneMonth => new DatePeriod(
				// @phpstan-ignore-next-line
				new DateTimeImmutable('-1 month'),
				new DateInterval('P1D'),
				// @phpstan-ignore-next-line
				new DateTimeImmutable('today'),
			),
			RangeEnum::ThreeMonths => new DatePeriod(
				// @phpstan-ignore-next-line
				new DateTimeImmutable('-3 months'),
				new DateInterval('P1D'),
				// @phpstan-ignore-next-line
				new DateTimeImmutable('today'),
			),
			RangeEnum::SixMonths => new DatePeriod(
				// @phpstan-ignore-next-line
				new DateTimeImmutable('-3 months'),
				new DateInterval('P1W'),
				// @phpstan-ignore-next-line
				new DateTimeImmutable('today'),
			),
			RangeEnum::YTD => new DatePeriod(
				// @phpstan-ignore-next-line
				new DateTimeImmutable('first day of january this year'),
				new DateInterval('P1W'),
				// @phpstan-ignore-next-line
				new DateTimeImmutable('today'),
			),
			RangeEnum::OneYear => new DatePeriod(
				// @phpstan-ignore-next-line
				new DateTimeImmutable('-1 year'),
				new DateInterval('P1W'),
				// @phpstan-ignore-next-line
				new DateTimeImmutable('today'),
			),
			RangeEnum::All => new DatePeriod(
				// @phpstan-ignore-next-line
				$firstDate ?? new DateTimeImmutable(self::FirstDate),
				new DateInterval('P1M'),
				// @phpstan-ignore-next-line
				new DateTimeImmutable('today'),
			),
		};
	}
}
