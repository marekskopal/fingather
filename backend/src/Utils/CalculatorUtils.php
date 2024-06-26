<?php

declare(strict_types=1);

namespace FinGather\Utils;

use Decimal\Decimal;

final class CalculatorUtils
{
	private const int DaysInYear = 365;

	public static function diffToPercentage(Decimal $valueOld, Decimal $valueNew): float
	{
		if ($valueOld->isZero()) {
			return 0.0;
		}

		$percentage = abs(self::toPercentage($valueOld->sub($valueNew), $valueOld));

		if ($valueOld > $valueNew) {
			return -$percentage;
		}

		return $percentage;
	}

	public static function toPercentage(Decimal $value, Decimal $total): float
	{
		if ($total->isZero()) {
			return 0.0;
		}

		return round($value->div($total)->mul(100)->toFloat(), 2);
	}

	public static function toPercentagePerAnnum(float $percentage, int $days): float
	{
		if ($percentage === 0.0) {
			return 0.0;
		}

		if ($days < self::DaysInYear) {
			$days = self::DaysInYear;
		}

		return round($percentage / ($days / self::DaysInYear), 2);
	}
}
