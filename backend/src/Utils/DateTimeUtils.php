<?php

declare(strict_types=1);

namespace FinGather\Utils;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use FinGather\Dto\Enum\RangeEnum;

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
				new \Safe\DateTimeImmutable('-1 week'),
				new DateInterval('P1D'),
				new \Safe\DateTimeImmutable('today'),
			),
			RangeEnum::OneMonth => new DatePeriod(
				new \Safe\DateTimeImmutable('-1 month'),
				new DateInterval('P1D'),
				new \Safe\DateTimeImmutable('today'),
			),
			RangeEnum::ThreeMonths => new DatePeriod(
				new \Safe\DateTimeImmutable('-3 months'),
				new DateInterval('P1D'),
				new \Safe\DateTimeImmutable('today'),
			),
			RangeEnum::SixMonths => new DatePeriod(
				new \Safe\DateTimeImmutable('-3 months'),
				new DateInterval('P1W'),
				new \Safe\DateTimeImmutable('today'),
			),
			RangeEnum::YTD => new DatePeriod(
				new \Safe\DateTimeImmutable('first day of january this year'),
				new DateInterval('P1W'),
				new \Safe\DateTimeImmutable('today'),
			),
			RangeEnum::OneYear => new DatePeriod(
				new \Safe\DateTimeImmutable('-1 year'),
				new DateInterval('P1W'),
				new \Safe\DateTimeImmutable('today'),
			),
			RangeEnum::All => new DatePeriod(
				$firstDate ?? new \Safe\DateTimeImmutable(self::FirstDate),
				new DateInterval('P1M'),
				new \Safe\DateTimeImmutable('today'),
			),
		};
	}
}
