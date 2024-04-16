<?php

declare(strict_types=1);

namespace FinGather\Tests\Utils;

use DateTimeImmutable;
use FinGather\Dto\Enum\RangeEnum;
use FinGather\Helper\DatePeriod;
use FinGather\Utils\DateTimeUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DateTimeUtils::class)]
#[CoversClass(DatePeriod::class)]
class DateTimeUtilsTest extends TestCase
{
	public function testFormatZulu(): void
	{
		$dateTime = new DateTimeImmutable('2021-01-01T12:01:02');

		$this->assertSame('2021-01-01T12:01:02Z', DateTimeUtils::formatZulu($dateTime));
	}

	public function testGetDatePeriod(): void
	{
		$datePeriod = DateTimeUtils::getDatePeriod(RangeEnum::SevenDays);
		$array = iterator_to_array($datePeriod->getIterator());

		$this->assertSame(8, count($array));
	}
}
