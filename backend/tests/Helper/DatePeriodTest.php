<?php

declare(strict_types=1);

namespace FinGather\Tests\Helper;

use DateInterval;
use FinGather\Helper\DatePeriod;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable;

#[CoversClass(DatePeriod::class)]
final class DatePeriodTest extends TestCase
{
	public function testGetIterator(): void
	{
		$startDate = new DateTimeImmutable('2021-01-01');
		$endDate = new DateTimeImmutable('2021-01-03');

		$datePeriod = new DatePeriod($startDate, new DateInterval('P1D'), $endDate);

		$array = iterator_to_array($datePeriod->getIterator(), false);

		self::assertCount(3, $array);
	}
}
