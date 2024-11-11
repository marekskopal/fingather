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

	public static function getDatePeriod(
		RangeEnum $range,
		?DateTimeImmutable $customRangeFrom = null,
		?DateTimeImmutable $customRangeTo = null,
		?DateTimeImmutable $firstDate = null,
		bool $shiftStartDate = false,
	): DatePeriod {
		return match ($range) {
			RangeEnum::SevenDays => new DatePeriod(
				(new DateTimeImmutable('-1 week' . ($shiftStartDate ? ' -1 day' : '')))->setTime(0, 0),
				new DateInterval('P1D'),
				new DateTimeImmutable('today'),
			),
			RangeEnum::OneMonth => new DatePeriod(
				(new DateTimeImmutable('-1 month' . ($shiftStartDate ? ' -1 day' : '')))->setTime(0, 0),
				new DateInterval('P1D'),
				new DateTimeImmutable('today'),
			),
			RangeEnum::ThreeMonths => new DatePeriod(
				(new DateTimeImmutable('-3 months' . ($shiftStartDate ? ' -1 day' : '')))->setTime(0, 0),
				new DateInterval('P1D'),
				new DateTimeImmutable('today'),
			),
			RangeEnum::SixMonths => new DatePeriod(
				(new DateTimeImmutable('-3 months' . ($shiftStartDate ? ' -1 week' : '')))->setTime(0, 0),
				new DateInterval('P1W'),
				new DateTimeImmutable('today'),
			),
			RangeEnum::YTD => new DatePeriod(
				(new DateTimeImmutable('first day of january this year' . ($shiftStartDate ? ' -1 week' : '')))->setTime(0, 0),
				new DateInterval('P1W'),
				new DateTimeImmutable('today'),
			),
			RangeEnum::OneYear => new DatePeriod(
				(new DateTimeImmutable('-1 year' . ($shiftStartDate ? ' -1 week' : '')))->setTime(0, 0),
				new DateInterval('P1W'),
				new DateTimeImmutable('today'),
			),
			RangeEnum::All => (function () use ($firstDate, $shiftStartDate): DatePeriod {
				$startDate = self::getStartDate($firstDate, $shiftStartDate);
				$endDate = new DateTimeImmutable('today');

				return new DatePeriod(
					$startDate,
					self::getIntervalForDates($startDate, $endDate),
					$endDate,
				);
			})(),
			RangeEnum::Custom => (function () use ($customRangeFrom, $customRangeTo, $shiftStartDate): DatePeriod {
				$startDate = $customRangeFrom ?? self::getStartDate($customRangeFrom, $shiftStartDate);
				$endDate = $customRangeTo ?? new DateTimeImmutable('today');

				return new DatePeriod(
					$startDate,
					self::getIntervalForDates($startDate, $endDate),
					$endDate,
				);
			})(),
		};
	}

	public static function setStartOfDateTime(DateTimeImmutable $dateTime): DateTimeImmutable
	{
		return $dateTime->setTime(0, 0);
	}

	public static function setEndOfDateTime(DateTimeImmutable $dateTime): DateTimeImmutable
	{
		return $dateTime->setTime(23, 59, 59, 999999);
	}

	private static function getStartDate(?DateTimeImmutable $firstDate = null, bool $shiftStartDate = false): DateTimeImmutable
	{
		return $shiftStartDate
			? (($firstDate ?? new DateTimeImmutable(self::FirstDate))->sub(DateInterval::createFromDateString('1 month'))->setTime(0, 0))
			: (($firstDate ?? new DateTimeImmutable(self::FirstDate))->setTime(0, 0));
	}

	private static function getIntervalForDates(DateTimeImmutable $startDate, DateTimeImmutable $endDate): DateInterval
	{
		$diffInterval = $endDate->diff($startDate);

		if ($diffInterval->y > 0) {
			return new DateInterval('P1M');
		}

		if ($diffInterval->m > 0) {
			return new DateInterval('P1W');
		}

		return new DateInterval('P1D');
	}
}
