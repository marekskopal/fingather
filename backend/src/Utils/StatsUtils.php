<?php

declare(strict_types=1);

namespace FinGather\Utils;

final readonly class StatsUtils
{
	/** @param list<float> $data */
	private static function mean(array $data): float
	{
		$n = count($data);
		if ($n === 0) {
			return 0.0;
		}

		return array_sum($data) / $n;
	}

	/**
	 * Sample variance (Bessel's correction).
	 *
	 * @param list<float> $data
	 */
	public static function variance(array $data): float
	{
		$n = count($data);
		if ($n < 2) {
			return 0.0;
		}

		$mean = self::mean($data);
		$sumSq = 0.0;
		foreach ($data as $x) {
			$sumSq += ($x - $mean) ** 2;
		}

		return $sumSq / ($n - 1);
	}

	/**
	 * @param list<float> $x
	 * @param list<float> $y
	 */
	public static function covariance(array $x, array $y): float
	{
		$n = count($x);
		if ($n < 2) {
			return 0.0;
		}

		$meanX = self::mean($x);
		$meanY = self::mean($y);
		$sum = 0.0;
		for ($i = 0; $i < $n; $i++) {
			$sum += ($x[$i] - $meanX) * ($y[$i] - $meanY);
		}

		return $sum / ($n - 1);
	}

	/**
	 * Pearson correlation. Series are sliced to a common length;
	 * callers should align the inputs by index/date themselves.
	 *
	 * @param list<float> $x
	 * @param list<float> $y
	 */
	public static function pearsonCorrelation(array $x, array $y): float
	{
		$minLen = min(count($x), count($y));
		if ($minLen < 2) {
			return 0.0;
		}

		$xSlice = array_slice($x, 0, $minLen);
		$ySlice = array_slice($y, 0, $minLen);

		$stdX = sqrt(self::variance($xSlice));
		$stdY = sqrt(self::variance($ySlice));

		if ($stdX === 0.0 || $stdY === 0.0) {
			return 0.0;
		}

		return self::covariance($xSlice, $ySlice) / ($stdX * $stdY);
	}
}
