<?php

declare(strict_types=1);

namespace FinGather\Tests\Comparator;

use Decimal\Decimal;
use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Comparator\ComparisonFailure;

/**
 * Compares Decimals by numeric value in assertEquals().
 *
 * PHPUnit's object comparator looks at the exported properties, which depend on the ext-decimal version:
 * 1.5.0 exposes none (so any two Decimals were "equal"), 1.5.3 exposes the string representation
 * (so 2.5 and 2.50 differ).
 */
final class DecimalComparator extends Comparator
{
	public function accepts(mixed $expected, mixed $actual): bool
	{
		return $expected instanceof Decimal && $actual instanceof Decimal;
	}

	public function assertEquals(
		mixed $expected,
		mixed $actual,
		float $delta = 0.0,
		bool $canonicalize = false,
		bool $ignoreCase = false,
	): void
	{
		assert($expected instanceof Decimal && $actual instanceof Decimal);

		$equals = $delta === 0.0
			? $expected->equals($actual)
			: $expected->sub($actual)->abs()->compareTo(new Decimal((string) $delta)) <= 0;

		if ($equals) {
			return;
		}

		throw new ComparisonFailure(
			$expected,
			$actual,
			$expected->toString(),
			$actual->toString(),
			'Failed asserting that two Decimals are equal.',
		);
	}
}
