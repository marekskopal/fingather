<?php

declare(strict_types=1);

namespace FinGather\Tests\Utils;

use DateTimeImmutable;
use FinGather\Dto\Enum\RangeEnum;
use FinGather\Helper\DatePeriod;
use FinGather\Utils\DateTimeUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(DateTimeUtils::class)]
#[CoversClass(DatePeriod::class)]
final class DateTimeUtilsTest extends TestCase
{
	public function testFormatZulu(): void
	{
		$dateTime = new DateTimeImmutable('2021-01-01T12:01:02');

		self::assertSame('2021-01-01T12:01:02Z', DateTimeUtils::formatZulu($dateTime));
	}

	#[TestWith([RangeEnum::SevenDays, null, false, 8])]
	#[TestWith([RangeEnum::SevenDays, null, true, 9])]
	#[TestWith([RangeEnum::All, new DateTimeImmutable('-1 year'), false, 13])]
	#[TestWith([RangeEnum::All, new DateTimeImmutable('-1 year'), true, 14])]
	public function testGetDatePeriod(RangeEnum $range, ?DateTimeImmutable $firstDate, bool $shiftStartDate, int $expectedCount): void
	{
		$datePeriod = DateTimeUtils::getDatePeriod(
			range: $range,
			customRangeFrom: null,
			customRangeTo: null,
			firstDate: $firstDate,
			shiftStartDate: $shiftStartDate,
		);
		$array = iterator_to_array($datePeriod->getIterator(), false);

		self::assertCount($expectedCount, $array);
	}

	public function testSetStartOfDateTime(): void
	{
		$dateTime = new DateTimeImmutable('2021-01-01T12:01:02');
		$dateTime = DateTimeUtils::setStartOfDateTime($dateTime);

		self::assertSame('2021-01-01 00:00:00:000000', $dateTime->format('Y-m-d H:i:s:u'));
	}

	public function testSetEndOfDateTime(): void
	{
		$dateTime = new DateTimeImmutable('2021-01-01T12:01:02');
		$dateTime = DateTimeUtils::setEndOfDateTime($dateTime);

		self::assertSame('2021-01-01 23:59:59:999999', $dateTime->format('Y-m-d H:i:s:u'));
	}
}
