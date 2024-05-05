<?php

declare(strict_types=1);

namespace FinGather\Tests\Utils;

use FinGather\Dto\Enum\RangeEnum;
use FinGather\Helper\DatePeriod;
use FinGather\Utils\DateTimeUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable;

#[CoversClass(DateTimeUtils::class)]
#[CoversClass(DatePeriod::class)]
final class DateTimeUtilsTest extends TestCase
{
	public function testFormatZulu(): void
	{
		$dateTime = new DateTimeImmutable('2021-01-01T12:01:02');

		self::assertSame('2021-01-01T12:01:02Z', DateTimeUtils::formatZulu($dateTime));
	}

	public function testGetDatePeriod(): void
	{
		$datePeriod = DateTimeUtils::getDatePeriod(RangeEnum::SevenDays);
		$array = iterator_to_array($datePeriod->getIterator());

		self::assertCount(8, $array);
	}
}
