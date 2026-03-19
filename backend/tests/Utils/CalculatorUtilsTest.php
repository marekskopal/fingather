<?php

declare(strict_types=1);

namespace FinGather\Tests\Utils;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Service\Provider\Dto\SplitDto;
use FinGather\Utils\CalculatorUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(CalculatorUtils::class)]
final class CalculatorUtilsTest extends TestCase
{
	#[TestWith([0.0, 0.0, 0.0])]
	#[TestWith([198442.53, 243315.52, 22.61])]
	#[TestWith([243315.52, 198442.53, -18.44])]
	#[TestWith([-530.50, -5723.64, -978.91])]
	public function testDiffToPercentage(float $valueOld, float $valueNew, float $expected): void
	{
		$diffToPercentage = CalculatorUtils::diffToPercentage(
			new Decimal((string) $valueOld),
			new Decimal((string) $valueNew),
		);

		self::assertSame($expected, $diffToPercentage);
	}

	#[TestWith([0.0, 0.0, 0.0])]
	#[TestWith([198442.53, 243315.52, 81.56])]
	#[TestWith([243315.52, 198442.53, 122.61])]
	public function testToPercentage(float $value, float $total, float $expected): void
	{
		$toPercentage = CalculatorUtils::toPercentage(new Decimal((string) $value), new Decimal((string) $total));

		self::assertSame($expected, $toPercentage);
	}

	#[TestWith([0.0, 50, 0.0])]
	#[TestWith([10.0, 50, 10.0])]
	#[TestWith([10.0, 730, 5.0])]
	public function testToPercentagePerAnnum(float $percentage, int $days, float $expected): void
	{
		$toPercentage = CalculatorUtils::toPercentagePerAnnum($percentage, $days);

		self::assertSame($expected, $toPercentage);
	}

	public function testCountSplitFactorNoSplits(): void
	{
		$result = CalculatorUtils::countSplitFactor(
			new DateTimeImmutable('2024-01-01'),
			new DateTimeImmutable('2024-12-31'),
			[],
		);

		self::assertSame('1', (string) $result);
	}

	public function testCountSplitFactorSplitWithinRange(): void
	{
		$splits = [
			new SplitDto(new DateTimeImmutable('2024-06-01'), new Decimal('2')),
		];

		$result = CalculatorUtils::countSplitFactor(
			new DateTimeImmutable('2024-01-01'),
			new DateTimeImmutable('2024-12-31'),
			$splits,
		);

		self::assertSame('2', (string) $result);
	}

	public function testCountSplitFactorSplitOutsideRange(): void
	{
		$splits = [
			new SplitDto(new DateTimeImmutable('2023-06-01'), new Decimal('2')),
		];

		$result = CalculatorUtils::countSplitFactor(
			new DateTimeImmutable('2024-01-01'),
			new DateTimeImmutable('2024-12-31'),
			$splits,
		);

		self::assertSame('1', (string) $result);
	}

	public function testCountSplitFactorSplitOnBoundary(): void
	{
		$dateFrom = new DateTimeImmutable('2024-01-01');
		$dateTo = new DateTimeImmutable('2024-12-31');

		$splits = [
			new SplitDto($dateFrom, new Decimal('3')),
			new SplitDto($dateTo, new Decimal('2')),
		];

		$result = CalculatorUtils::countSplitFactor($dateFrom, $dateTo, $splits);

		self::assertSame('6', (string) $result);
	}

	public function testCountSplitFactorMultipleSplits(): void
	{
		$splits = [
			new SplitDto(new DateTimeImmutable('2024-03-01'), new Decimal('2')),
			new SplitDto(new DateTimeImmutable('2024-07-01'), new Decimal('3')),
			new SplitDto(new DateTimeImmutable('2025-01-01'), new Decimal('5')),
		];

		$result = CalculatorUtils::countSplitFactor(
			new DateTimeImmutable('2024-01-01'),
			new DateTimeImmutable('2024-12-31'),
			$splits,
		);

		self::assertSame('6', (string) $result);
	}

	#[TestWith([0.0, 0.0])]
	#[TestWith([3.14159, 3.14])]
	#[TestWith([-3.14159, -3.14])]
	#[TestWith([3.145, 3.15])]
	#[TestWith([100.0, 100.0])]
	#[TestWith([99.999, 100.0])]
	public function testRoundPercentage(float $value, float $expected): void
	{
		self::assertSame($expected, CalculatorUtils::roundPercentage($value));
	}

	/** @param float[] $percentages */
	#[TestWith([[], 0.0])]
	#[TestWith([[10.0], 10.0])]
	#[TestWith([[10.0, 20.0, 30.0], 60.0])]
	#[TestWith([[10.0, -30.0], -20.0])]
	// float imprecision: 1.23 + 4.56 + 7.89 = 13.680000000000001 without rounding

	#[TestWith([[1.23, 4.56, 7.89], 13.68])]
	public function testSumPercentages(array $percentages, float $expected): void
	{
		self::assertSame($expected, CalculatorUtils::sumPercentages(...$percentages));
	}

	public function testFloatToDecimalDefaultPrecision(): void
	{
		$result = CalculatorUtils::floatToDecimal(1.123456789012345);

		self::assertSame('1.12345679', (string) $result);
	}

	public function testFloatToDecimalCustomPrecision(): void
	{
		$result = CalculatorUtils::floatToDecimal(123.4567891, 4);

		self::assertSame('123.4568', (string) $result);
	}

	public function testFloatToDecimalStripsFloatNoise(): void
	{
		// 0.1 + 0.2 in float = 0.30000000000000002, should be truncated to 8dp
		$result = CalculatorUtils::floatToDecimal(0.1 + 0.2);

		self::assertSame('0.3', (string) $result);
	}

	public function testFloatToDecimalZero(): void
	{
		$result = CalculatorUtils::floatToDecimal(0.0);

		self::assertSame('0', (string) $result);
	}

	public function testFloatToDecimalNegative(): void
	{
		$result = CalculatorUtils::floatToDecimal(-1.5, 2);

		self::assertSame('-1.5', (string) $result);
	}
}
