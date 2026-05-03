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
	 * month-over-month returns. Tickers without a return for a given month (own data missing AND no
	 * proxy fallback) are skipped for that month and weights are renormalised over the available subset.
	 *
	 * If `$proxyTickerIdByTickerId` maps a ticker to a proxy ticker, that proxy's monthly return is
	 * substituted for any month the ticker itself can't cover (typically because the ticker is younger
	 * than the lookback window, but also for gaps in its own data). This is what extends the empirical
	 * sample back through events like 2000–2002 and 2008.
	 *
	 * @param list<TickerWeightDto> $tickerWeights
	 * @param array<int, int|null> $proxyTickerIdByTickerId map of held-ticker id → proxy ticker id
	 * @return list<float>
	 */
	public function buildMonthlyCompositeReturns(
		array $tickerWeights,
		DateTimeImmutable $toDate,
		int $historyYears,
		array $proxyTickerIdByTickerId = [],
	): array {
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

		// Per ticker, a (monthKey → multiplier) map already incorporating proxy splice.
		/** @var array<int, array<string, float>> $tickerMultipliers */
		$tickerMultipliers = [];
		foreach ($tickerWeights as $tickerWeight) {
			$tickerMultipliers[$tickerWeight->tickerId] = $this->buildPerTickerMonthlyReturns(
				$tickerWeight->tickerId,
				$proxyTickerIdByTickerId[$tickerWeight->tickerId] ?? null,
				$fromDate,
				$toDate,
			);
		}

		$sortedMonths = $this->collectSortedMonths($tickerMultipliers);
		if (count($sortedMonths) === 0) {
			return [];
		}

		$compositeReturns = [];
		foreach ($sortedMonths as $monthKey) {
			$weightSum = 0.0;
			$weightedReturn = 0.0;
			foreach ($tickerWeights as $tickerWeight) {
				$multiplier = $tickerMultipliers[$tickerWeight->tickerId][$monthKey] ?? null;
				if ($multiplier === null) {
					continue;
				}
				$weightedReturn += $multiplier * $tickerWeight->weight;
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
	 * Returns a (monthKey → multiplier) map of month-over-month returns for a single ticker, with
	 * the proxy ticker filling in for any month the held ticker can't cover (own prev or curr close
	 * missing). Used per-ticker before composing across the basket.
	 *
	 * @return array<string, float>
	 */
	private function buildPerTickerMonthlyReturns(
		int $tickerId,
		?int $proxyTickerId,
		DateTimeImmutable $fromDate,
		DateTimeImmutable $toDate,
	): array {
		$tickerCloses = $this->extractMonthEndCloses($tickerId, $fromDate, $toDate);
		$proxyCloses = $proxyTickerId !== null
			? $this->extractMonthEndCloses($proxyTickerId, $fromDate, $toDate)
			: [];

		$allMonths = $tickerCloses + $proxyCloses;
		if (count($allMonths) < 2) {
			return [];
		}

		$sortedMonths = array_keys($allMonths);
		sort($sortedMonths);

		$multipliers = [];
		for ($i = 1, $count = count($sortedMonths); $i < $count; $i++) {
			$prevMonth = $sortedMonths[$i - 1];
			$currMonth = $sortedMonths[$i];

			$prevTickerClose = $tickerCloses[$prevMonth] ?? null;
			$currTickerClose = $tickerCloses[$currMonth] ?? null;
			if ($prevTickerClose !== null && $currTickerClose !== null && $prevTickerClose > 0.0) {
				$multipliers[$currMonth] = $currTickerClose / $prevTickerClose;
				continue;
			}

			$prevProxyClose = $proxyCloses[$prevMonth] ?? null;
			$currProxyClose = $proxyCloses[$currMonth] ?? null;
			if ($prevProxyClose !== null && $currProxyClose !== null && $prevProxyClose > 0.0) {
				$multipliers[$currMonth] = $currProxyClose / $prevProxyClose;
			}
		}

		return $multipliers;
	}

	/**
	 * Runs `simulations` independent paths over `months` periods. Each path uses circular block
	 * bootstrap: it picks a random start index, then walks `blockSize` consecutive monthly returns
	 * before drawing a new start (wrapping around at the sample end). Block bootstrap preserves
	 * volatility clustering and short autocorrelation that an i.i.d. resample would destroy, which
	 * is the source of realistic sequence-of-returns risk in the resulting bands.
	 *
	 * Pass `blockSize = 1` for plain i.i.d. resampling. Effective block size is clamped to the
	 * sample size.
	 *
	 * The result holds the 10th, 50th and 90th percentiles per month across all paths.
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
		int $blockSize = 1,
	): SimulationResultDto {
		$randomizer ??= new Randomizer(new Mt19937());
		$sampleSize = count($monthlyReturns);
		$effectiveBlock = max(1, min($blockSize, $sampleSize));

		// pathValues[i] holds the value at month i+1 across all simulations.
		$pathValues = array_fill(0, $months, []);

		for ($s = 0; $s < $simulations; $s++) {
			$value = $startValue;
			$blockStart = 0;
			// force a fresh draw on the first iteration
			$offsetInBlock = $effectiveBlock;
			for ($m = 0; $m < $months; $m++) {
				if ($offsetInBlock >= $effectiveBlock) {
					$blockStart = $randomizer->getInt(0, $sampleSize - 1);
					$offsetInBlock = 0;
				}
				$index = ($blockStart + $offsetInBlock) % $sampleSize;
				$value = ($value + $amount) * $monthlyReturns[$index];
				$pathValues[$m][] = $value;
				$offsetInBlock++;
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

	/**
	 * Multiplicatively rescales the empirical monthly returns so their arithmetic mean equals
	 * `targetMean`. Volatility shape (relative dispersion, ordering) is preserved; only the central
	 * tendency shifts. Use this to align a bootstrap sample with a forward-looking expected return
	 * (e.g. a shrunk CAGR) without throwing away the realised distribution.
	 *
	 * Returns the input unchanged if it's empty or its mean is non-positive (which would make the
	 * scale factor undefined or sign-flipping).
	 *
	 * @param list<float> $monthlyReturns
	 * @return list<float>
	 */
	public function scaleMonthlyReturnsToMean(array $monthlyReturns, float $targetMean): array
	{
		$count = count($monthlyReturns);
		if ($count === 0) {
			return $monthlyReturns;
		}

		$mean = array_sum($monthlyReturns) / $count;
		if ($mean <= 0.0) {
			return $monthlyReturns;
		}

		$factor = $targetMean / $mean;
		return array_map(static fn (float $r): float => $r * $factor, $monthlyReturns);
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
