<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use DateInterval;
use DateTimeImmutable;
use FinGather\Model\Entity\TickerData;
use FinGather\Service\DataCalculator\Dto\SimulationResultDto;
use FinGather\Service\DataCalculator\Dto\TickerWeightDto;
use FinGather\Service\Provider\TickerDataProviderInterface;
use Random\Engine\Mt19937;
use Random\Randomizer;

final readonly class DcaPlanMonteCarloSimulator
{
	public function __construct(private TickerDataProviderInterface $tickerDataProvider,)
	{
	}

	/**
	 * Builds the empirical sample of monthly composite returns (as multipliers, e.g. 1.012 for +1.2 %)
	 * for the given weighted ticker basket. Each month's return is the weighted average of per-ticker
	 * month-over-month closing-price returns. Tickers without a close in the given month are skipped
	 * for that month and weights are renormalised over the available subset.
	 *
	 * @param list<TickerWeightDto> $tickerWeights
	 * @return list<float>
	 */
	public function buildMonthlyCompositeReturns(array $tickerWeights, DateTimeImmutable $toDate, int $historyYears,): array
	{
		if (count($tickerWeights) === 0) {
			return [];
		}

		$totalWeight = 0.0;
		foreach ($tickerWeights as $tickerWeight) {
			$totalWeight += $tickerWeight->weight;
		}
		if ($totalWeight <= 0.0) {
			return [];
		}

		$fromDate = $toDate->sub(new DateInterval('P' . $historyYears . 'Y'));

		// tickerCloses[tickerId][YYYY-MM] = float close (last close of the month).
		/** @var array<int, array<string, float>> $tickerCloses */
		$tickerCloses = [];
		foreach ($tickerWeights as $tickerWeight) {
			$tickerCloses[$tickerWeight->tickerId] = $this->extractMonthEndCloses($tickerWeight->tickerId, $fromDate, $toDate);
		}

		$months = $this->collectSortedMonths($tickerCloses);
		if (count($months) < 2) {
			return [];
		}

		$compositeReturns = [];
		for ($i = 1, $count = count($months); $i < $count; $i++) {
			$prevMonth = $months[$i - 1];
			$currMonth = $months[$i];

			$weightSum = 0.0;
			$weightedReturn = 0.0;
			foreach ($tickerWeights as $tickerWeight) {
				$prevClose = $tickerCloses[$tickerWeight->tickerId][$prevMonth] ?? null;
				$currClose = $tickerCloses[$tickerWeight->tickerId][$currMonth] ?? null;
				if ($prevClose === null || $currClose === null || $prevClose <= 0.0) {
					continue;
				}

				$tickerReturn = $currClose / $prevClose;
				$weightedReturn += $tickerReturn * $tickerWeight->weight;
				$weightSum += $tickerWeight->weight;
			}

			if ($weightSum <= 0.0) {
				continue;
			}

			$compositeReturns[] = $weightedReturn / $weightSum;
		}

		return $compositeReturns;
	}

	/**
	 * Runs `simulations` independent paths over `months` periods. Each path bootstraps monthly returns
	 * with replacement from `monthlyReturns`, applies them to the running portfolio value plus the fixed
	 * `amount` contribution per month, and stores the resulting per-month value. The result holds the
	 * 10th, 50th and 90th percentiles per month across all paths.
	 *
	 * @param list<float> $monthlyReturns empirical monthly multipliers (1.012 = +1.2 %)
	 */
	public function simulate(
		array $monthlyReturns,
		float $startValue,
		float $amount,
		int $months,
		int $simulations,
		?Randomizer $randomizer = null,
	): SimulationResultDto {
		$randomizer ??= new Randomizer(new Mt19937());
		$sampleSize = count($monthlyReturns);

		// pathValues[i] holds the value at month i+1 across all simulations.
		$pathValues = array_fill(0, $months, []);

		for ($s = 0; $s < $simulations; $s++) {
			$value = $startValue;
			for ($m = 0; $m < $months; $m++) {
				$value = ($value + $amount) * $monthlyReturns[$randomizer->getInt(0, $sampleSize - 1)];
				$pathValues[$m][] = $value;
			}
		}

		$p10 = [];
		$p50 = [];
		$p90 = [];
		foreach ($pathValues as $values) {
			sort($values);
			$p10[] = self::percentile($values, 10.0);
			$p50[] = self::percentile($values, 50.0);
			$p90[] = self::percentile($values, 90.0);
		}

		return new SimulationResultDto(p10: $p10, p50: $p50, p90: $p90);
	}

	/** @return array<string, float> month key 'YYYY-MM' → last close in that month */
	private function extractMonthEndCloses(int $tickerId, DateTimeImmutable $fromDate, DateTimeImmutable $toDate): array
	{
		$closes = [];
		// Repository returns rows ordered by date DESC. The first time we see a month is its last close.
		foreach ($this->tickerDataProvider->getTickerDatasByTickerId($tickerId, $fromDate, $toDate) as $tickerData) {
			$monthKey = $this->monthKey($tickerData);
			if (isset($closes[$monthKey])) {
				continue;
			}

			$closes[$monthKey] = $tickerData->close->toFloat();
		}

		return $closes;
	}

	/**
	 * @param array<int, array<string, float>> $tickerCloses
	 * @return list<string> sorted ascending
	 */
	private function collectSortedMonths(array $tickerCloses): array
	{
		$months = [];
		foreach ($tickerCloses as $monthMap) {
			foreach (array_keys($monthMap) as $monthKey) {
				$months[$monthKey] = true;
			}
		}

		$sorted = array_keys($months);
		sort($sorted);

		return $sorted;
	}

	private function monthKey(TickerData $tickerData): string
	{
		return $tickerData->date->format('Y-m');
	}

	/** @param list<float> $sortedValues already sorted ascending */
	private static function percentile(array $sortedValues, float $percentile): float
	{
		$count = count($sortedValues);
		if ($count === 0) {
			return 0.0;
		}
		if ($count === 1) {
			return $sortedValues[0];
		}

		$rank = $percentile / 100.0 * ($count - 1);
		$lower = (int) floor($rank);
		$upper = (int) ceil($rank);
		if ($lower === $upper) {
			return $sortedValues[$lower];
		}

		$weight = $rank - $lower;
		return $sortedValues[$lower] * (1.0 - $weight) + $sortedValues[$upper] * $weight;
	}
}
