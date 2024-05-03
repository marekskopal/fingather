<?php

declare(strict_types=1);

namespace FinGather\Tests\Utils;

use Decimal\Decimal;
use FinGather\Utils\CalculatorUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(CalculatorUtils::class)]
final class CalculatorUtilsTest extends TestCase
{
	#[TestWith([198442.53, 243315.52, 22.61])]
	#[TestWith([243315.52, 198442.53, -18.44])]
	#[TestWith([-530.50, -5723.64, -978.91])]
	public function testDiffToPercentage(float $valueOld, float $valueNew, float $expected): void
	{
		$diffToPercentage = CalculatorUtils::diffToPercentage(
			new Decimal((string) $valueOld),
			new Decimal((string) $valueNew),
		);

		$this->assertSame($expected, $diffToPercentage);
	}

	#[TestWith([198442.53, 243315.52, 81.56])]
	#[TestWith([243315.52, 198442.53, 122.61])]
	public function testToPercentage(float $value, float $total, float $expected): void
	{
		$toPercentage = CalculatorUtils::toPercentage(new Decimal((string) $value), new Decimal((string) $total));

		$this->assertSame($expected, $toPercentage);
	}

	#[TestWith([10.0, 50, 10.0])]
	#[TestWith([10.0, 730, 5.0])]
	public function testToPercentagePerAnnum(float $percentage, int $days, float $expected): void
	{
		$toPercentage = CalculatorUtils::toPercentagePerAnnum($percentage, $days);

		$this->assertSame($expected, $toPercentage);
	}
}
