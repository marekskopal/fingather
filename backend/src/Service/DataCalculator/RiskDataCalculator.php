<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use DateTimeImmutable;
use FinGather\Dto\Enum\RangeEnum;
use FinGather\Dto\Enum\SamplingFrequencyEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\RiskDataDto;
use FinGather\Service\Provider\AssetDataProviderInterface;
use FinGather\Service\Provider\AssetProviderInterface;
use FinGather\Service\Provider\Dto\TickerDataAdjustedDto;
use FinGather\Service\Provider\PortfolioDataProviderInterface;
use FinGather\Service\Provider\TickerDataProviderInterface;
use FinGather\Service\Provider\TransactionProviderInterface;
use FinGather\Utils\DateTimeUtils;
use FinGather\Utils\StatsUtils;

final readonly class RiskDataCalculator
{
	private const int MaxCorrelationAssets = 20;
	private const int TradingDaysPerYear = 252;

	public function __construct(
		private PortfolioDataProviderInterface $portfolioDataProvider,
		private AssetProviderInterface $assetProvider,
		private AssetDataProviderInterface $assetDataProvider,
		private TickerDataProviderInterface $tickerDataProvider,
		private TransactionProviderInterface $transactionProvider,
	) {
	}

	public function calculate(
		User $user,
		Portfolio $portfolio,
		RangeEnum $range,
		?Ticker $benchmarkTicker,
		?DateTimeImmutable $customRangeFrom,
		?DateTimeImmutable $customRangeTo,
		SamplingFrequencyEnum $samplingFrequency = SamplingFrequencyEnum::Daily,
	): RiskDataDto {
		$firstTransaction = $this->transactionProvider->getFirstTransaction($user, $portfolio);
		if ($firstTransaction === null) {
			return new RiskDataDto(
				volatility: 0.0,
				maxDrawdown: 0.0,
				sharpeRatio: 0.0,
				beta: 0.0,
				correlationLabels: [],
				correlationMatrix: [],
			);
		}

		$datePeriod = DateTimeUtils::getDatePeriod(
			range: $range,
			customRangeFrom: $customRangeFrom,
			customRangeTo: $customRangeTo,
			firstDate: $firstTransaction->actionCreated,
			shiftStartDate: $range === RangeEnum::All,
		);

		/** @var list<float> $portfolioValues */
		$portfolioValues = [];
		$lastPortfolioData = null;

		foreach ($datePeriod as $dateTime) {
			/** @var DateTimeImmutable $dateTime */
			$portfolioData = $this->portfolioDataProvider->getPortfolioData($user, $portfolio, $dateTime);
			$portfolioValues[] = (float) (string) $portfolioData->value;
			$lastPortfolioData = $portfolioData;
		}

		if (count($portfolioValues) < 2 || $lastPortfolioData === null) {
			return new RiskDataDto(
				volatility: 0.0,
				maxDrawdown: 0.0,
				sharpeRatio: 0.0,
				beta: 0.0,
				correlationLabels: [],
				correlationMatrix: [],
			);
		}

		$portfolioReturns = $this->computeReturns($portfolioValues);

		$volatility = $this->computeVolatility($portfolioReturns);
		$maxDrawdown = $this->computeMaxDrawdown($portfolioValues);

		$annualReturn = $lastPortfolioData->returnPercentagePerAnnum;
		$sharpeRatio = $volatility > 0.0 ? $annualReturn / $volatility : 0.0;

		$beta = $this->computeBeta($portfolioReturns, $benchmarkTicker, $datePeriod->getStartDate(), new DateTimeImmutable('today'));

		[$correlationLabels, $correlationMatrix] = $this->computeCorrelationMatrix(
			$user,
			$portfolio,
			$datePeriod->getStartDate(),
			new DateTimeImmutable('today'),
			$samplingFrequency,
		);

		return new RiskDataDto(
			volatility: round($volatility, 4),
			maxDrawdown: round($maxDrawdown, 4),
			sharpeRatio: round($sharpeRatio, 4),
			beta: round($beta, 4),
			correlationLabels: $correlationLabels,
			correlationMatrix: $correlationMatrix,
		);
	}

	/**
	 * @param list<float> $values
	 * @return list<float>
	 */
	private function computeReturns(array $values): array
	{
		$returns = [];
		$count = count($values);
		for ($i = 1; $i < $count; $i++) {
			$prev = $values[$i - 1];
			if ($prev === 0.0) {
				$returns[] = 0.0;
				continue;
			}

			$returns[] = ($values[$i] - $prev) / $prev;
		}

		return $returns;
	}

	/**
	 * Annualised volatility as percentage.
	 *
	 * @param list<float> $returns
	 */
	private function computeVolatility(array $returns): float
	{
		return sqrt(StatsUtils::variance($returns)) * sqrt(self::TradingDaysPerYear) * 100;
	}

	/**
	 * Max drawdown as percentage (negative value).
	 *
	 * @param list<float> $values
	 */
	private function computeMaxDrawdown(array $values): float
	{
		$peak = $values[0];
		$maxDrawdown = 0.0;

		foreach ($values as $value) {
			if ($value > $peak) {
				$peak = $value;
			}

			if ($peak <= 0.0) {
				continue;
			}

			$drawdown = ($value - $peak) / $peak * 100;
			if ($drawdown < $maxDrawdown) {
				$maxDrawdown = $drawdown;
			}
		}

		return $maxDrawdown;
	}

	/** @param list<float> $portfolioReturns */
	private function computeBeta(
		array $portfolioReturns,
		?Ticker $benchmarkTicker,
		DateTimeImmutable $fromDate,
		DateTimeImmutable $toDate,
	): float {
		if ($benchmarkTicker === null || count($portfolioReturns) < 2) {
			return 0.0;
		}

		$tickerDatas = $this->tickerDataProvider->getAdjustedTickerDatas($benchmarkTicker, $fromDate, $toDate);
		if (count($tickerDatas) < 2) {
			return 0.0;
		}

		$benchmarkPrices = array_map(
			static fn(TickerDataAdjustedDto $td): float => (float) (string) $td->close,
			$tickerDatas,
		);
		$benchmarkReturns = $this->computeReturns($benchmarkPrices);

		$minLen = min(count($portfolioReturns), count($benchmarkReturns));
		if ($minLen < 2) {
			return 0.0;
		}

		$portfolioSlice = array_slice($portfolioReturns, 0, $minLen);
		$benchmarkSlice = array_slice($benchmarkReturns, 0, $minLen);

		$benchmarkVar = StatsUtils::variance($benchmarkSlice);
		if ($benchmarkVar === 0.0) {
			return 0.0;
		}

		return StatsUtils::covariance($portfolioSlice, $benchmarkSlice) / $benchmarkVar;
	}

	/**
	 * Pearson-correlation matrix for the top-N holdings, with returns aligned on the
	 * intersection of trading dates so tickers with different calendars
	 * (e.g. crypto + stocks) correlate correctly.
	 *
	 * @return array{list<string>, list<list<float>>}
	 */
	private function computeCorrelationMatrix(
		User $user,
		Portfolio $portfolio,
		DateTimeImmutable $fromDate,
		DateTimeImmutable $toDate,
		SamplingFrequencyEnum $samplingFrequency,
	): array {
		$today = new DateTimeImmutable('today');

		// Rank current open holdings by value to pick the top-N for the matrix.
		$assetValues = [];
		foreach ($this->assetProvider->getAssets(user: $user, portfolio: $portfolio, dateTime: $today) as $asset) {
			$assetData = $this->assetDataProvider->getAssetData($user, $portfolio, $asset, $today);
			if ($assetData === null || !$assetData->isOpen()) {
				continue;
			}

			$assetValues[$asset->ticker->ticker] = (float) (string) $assetData->value;
		}

		arsort($assetValues);
		$topAssets = array_slice($assetValues, 0, self::MaxCorrelationAssets, true);

		// Build a date => price map per ticker, then align all series on the intersection
		// of dates so index i in every returns array refers to the same calendar date.
		$assetPriceByDate = $this->collectPriceMaps($user, $portfolio, $topAssets, $fromDate, $toDate, $samplingFrequency);
		$commonDates = $this->intersectDateKeys($assetPriceByDate);
		if (count($commonDates) < 2) {
			return [[], []];
		}

		/** @var array<string, list<float>> $assetReturnSeries */
		$assetReturnSeries = [];
		foreach ($assetPriceByDate as $tickerSymbol => $priceByDate) {
			$alignedPrices = array_map(static fn(string $date): float => $priceByDate[$date], $commonDates);
			$returns = $this->computeReturns($alignedPrices);
			if (count($returns) < 2) {
				continue;
			}

			$assetReturnSeries[$tickerSymbol] = $returns;
		}

		$labels = array_keys($assetReturnSeries);
		$n = count($labels);
		if ($n === 0) {
			return [[], []];
		}

		/** @var list<list<float>> $matrix */
		$matrix = [];
		for ($i = 0; $i < $n; $i++) {
			$row = [];
			for ($j = 0; $j < $n; $j++) {
				$row[] = StatsUtils::pearsonCorrelation($assetReturnSeries[$labels[$i]], $assetReturnSeries[$labels[$j]]);
			}

			$matrix[] = $row;
		}

		return [$labels, $matrix];
	}

	/**
	 * @param array<string, float> $topAssets
	 * @return array<string, array<string, float>>
	 */
	private function collectPriceMaps(
		User $user,
		Portfolio $portfolio,
		array $topAssets,
		DateTimeImmutable $fromDate,
		DateTimeImmutable $toDate,
		SamplingFrequencyEnum $samplingFrequency,
	): array {
		$assetPriceByDate = [];
		foreach ($this->assetProvider->getAssets(user: $user, portfolio: $portfolio) as $asset) {
			$tickerSymbol = $asset->ticker->ticker;
			if (!array_key_exists($tickerSymbol, $topAssets)) {
				continue;
			}

			$tickerDatas = $this->tickerDataProvider->getAdjustedTickerDatas($asset->ticker, $fromDate, $toDate);
			if (count($tickerDatas) < 2) {
				continue;
			}

			// Bucket by sampling frequency, keeping the last close per bucket. ISO-week
			// (Y-\WW) and calendar-month (Y-m) buckets sort lexicographically the same
			// as chronologically, so the intersect+sort downstream is correct.
			$priceByDate = [];
			foreach ($tickerDatas as $tickerData) {
				$bucket = match ($samplingFrequency) {
					SamplingFrequencyEnum::Daily => $tickerData->date->format('Y-m-d'),
					SamplingFrequencyEnum::Weekly => $tickerData->date->format('o-\WW'),
					SamplingFrequencyEnum::Monthly => $tickerData->date->format('Y-m'),
				};
				$priceByDate[$bucket] = (float) (string) $tickerData->close;
			}

			$assetPriceByDate[$tickerSymbol] = $priceByDate;
		}

		return $assetPriceByDate;
	}

	/**
	 * @param array<string, array<string, float>> $assetPriceByDate
	 * @return list<string>
	 */
	private function intersectDateKeys(array $assetPriceByDate): array
	{
		$commonDates = null;
		foreach ($assetPriceByDate as $priceByDate) {
			$dates = array_keys($priceByDate);
			$commonDates = $commonDates === null
				? $dates
				: array_values(array_intersect($commonDates, $dates));
		}

		if ($commonDates === null) {
			return [];
		}

		sort($commonDates);

		return $commonDates;
	}
}
