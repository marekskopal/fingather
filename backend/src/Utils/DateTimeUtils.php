<?php

declare(strict_types=1);

namespace FinGather\Utils;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use FinGather\Dto\Enum\PortfolioDataRangeEnum;

final class DateTimeUtils
{
	private const FORMAT_ZULU = 'Y-m-d\TH:i:sp';

	public static function formatZulu(DateTimeImmutable|DateTime $dateTime): string
	{
		return $dateTime->format(self::FORMAT_ZULU);
	}

	public static function getDatePeriod(PortfolioDataRangeEnum $range, DateTimeImmutable $firstDate): DatePeriod
	{
		return match ($range) {
			PortfolioDataRangeEnum::SevenDays => new DatePeriod(
				new \Safe\DateTimeImmutable('-1 week'),
				new DateInterval('P1D'),
				new \Safe\DateTimeImmutable('today'),
			),
			PortfolioDataRangeEnum::OneMonth => new DatePeriod(
				new \Safe\DateTimeImmutable('-1 month'),
				new DateInterval('P1D'),
				new \Safe\DateTimeImmutable('today'),
			),
			PortfolioDataRangeEnum::ThreeMonths => new DatePeriod(
				new \Safe\DateTimeImmutable('-3 months'),
				new DateInterval('P1D'),
				new \Safe\DateTimeImmutable('today'),
			),
			PortfolioDataRangeEnum::SixMonths => new DatePeriod(
				new \Safe\DateTimeImmutable('-3 months'),
				new DateInterval('P1W'),
				new \Safe\DateTimeImmutable('today'),
			),
			PortfolioDataRangeEnum::YTD => new DatePeriod(
				new \Safe\DateTimeImmutable('first day this year'),
				new DateInterval('P1W'),
				new \Safe\DateTimeImmutable('today'),
			),
			PortfolioDataRangeEnum::OneYear => new DatePeriod(
				new \Safe\DateTimeImmutable('first day this year'),
				new DateInterval('P1W'),
				new \Safe\DateTimeImmutable('today'),
			),
			PortfolioDataRangeEnum::All => new DatePeriod(
				$firstDate,
				new DateInterval('P1M'),
				new \Safe\DateTimeImmutable('today'),
			),
		};
	}
}
