<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use DateInterval;
use DateTimeImmutable;
use FinGather\Dto\AssetWithPropertiesDto;
use FinGather\Dto\DcaPlanProjectionDto;
use FinGather\Dto\DcaPlanProjectionPointDto;
use FinGather\Dto\Enum\AssetOrderEnum;
use FinGather\Model\Entity\DcaPlan;
use FinGather\Model\Entity\Enum\DcaPlanTargetTypeEnum;
use FinGather\Model\Entity\Enum\TickerTypeEnum;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Strategy;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\ReturnRateDto;
use FinGather\Service\DataCalculator\Dto\TickerWeightDto;
use FinGather\Service\Provider\AssetWithPropertiesProviderInterface;
use FinGather\Service\Provider\ProxyAssetProviderInterface;
use FinGather\Service\Provider\TickerDataProviderInterface;
use FinGather\Utils\CalculatorUtils;

final readonly class DcaPlanDataCalculator
{
	/**
	 * Lookback for both the trailing-CAGR estimate and the Monte Carlo bootstrap. 25 years buys back
	 * the dot-com crash and the 2008 GFC, but only when proxy splicing is active for tickers that
	 * don't have that much of their own history (most do not).
	 */
	private const int HistoryYears = 25;
	private const int MinDays = 365;
	private const int MonthsPerYear = 12;
	private const float AvgSecondsPerMonth = 30.4375 * 24 * 3600;
	private const int DecimalPrecision = 8;
	private const int MaxSimulations = 50000;
	private const int MinHistoryMonthsForSimulation = 24;

	/**
	 * Long-run nominal equity return used as a Bayesian prior. A trailing 10y CAGR for the recent
	 * bull market sits well above realistic forward expectations (Vanguard/AQR/BlackRock CMAs cluster
	 * at 4–7% nominal); shrinking toward this prior pulls the projection back toward that range.
	 */
	private const float ExpectedReturnPriorAnnual = 7.0;

	/**
	 * Weight on the prior in the shrinkage blend (0 = pure trailing CAGR, 1 = pure prior). 0.5 is a
	 * deliberately strong shrink — trailing 10y CAGR is a noisy and biased estimator of the next 10y.
	 */
	private const float ExpectedReturnShrinkageWeight = 0.5;

	/**
	 * Block size for circular block bootstrap in the Monte Carlo simulator. Preserves volatility
	 * clustering and short autocorrelation present in monthly equity returns, so P10/P90 bands
	 * widen to reflect sequence-of-returns risk that i.i.d. resampling hides.
	 */
	private const int BootstrapBlockSize = 6;

	public function __construct(
		private TickerDataProviderInterface $tickerDataProvider,
		private AssetWithPropertiesProviderInterface $assetWithPropertiesProvider,
		private DcaPlanMonteCarloSimulator $monteCarloSimulator,
		private ProxyAssetProviderInterface $proxyAssetProvider,
	) {
	}

	public function calculateReturnRate(DcaPlan $dcaPlan): ReturnRateDto
	{
		$tickerWeights = $this->getTickerWeights($dcaPlan);

		if (count($tickerWeights) === 0) {
			return new ReturnRateDto(annual: 0.0, monthly: 0.0);
		}

		$weightedReturn = 0.0;
		$dataWeight = 0.0;
		foreach ($tickerWeights as $tickerWeight) {
			$tickerReturn = $this->calculateTickerReturnRate($tickerWeight->tickerId);
			if ($tickerReturn === null) {
				continue;
			}
			$weightedReturn += $tickerReturn * $tickerWeight->weight;
			$dataWeight += $tickerWeight->weight;
		}

		// No usable history at all: don't fabricate an expected return — keep zero.
		if ($dataWeight <= 0.0) {
			return new ReturnRateDto(annual: 0.0, monthly: 0.0);
		}

		$trailingCagr = $weightedReturn / $dataWeight;
		$expectedAnnual = self::ExpectedReturnShrinkageWeight * self::ExpectedReturnPriorAnnual
			+ (1.0 - self::ExpectedReturnShrinkageWeight) * $trailingCagr;

		$annualRate = CalculatorUtils::roundPercentage($expectedAnnual);
		$monthlyRate = ((1 + $annualRate / 100) ** (1 / self::MonthsPerYear) - 1) * 100;

		return new ReturnRateDto(annual: $annualRate, monthly: $monthlyRate);
	}

	public function getProjection(DcaPlan $dcaPlan, int $horizonYears = 10, bool $withCurrentValue = true): DcaPlanProjectionDto
	{
		$returnRate = $this->calculateReturnRate($dcaPlan);
		$monthlyRate = $returnRate->monthly / 100;
		$amount = $dcaPlan->amount->toFloat();
		$startValue = $withCurrentValue ? $this->getCurrentValue($dcaPlan) : 0.0;

		$maxMonths = $horizonYears * self::MonthsPerYear;
		if ($dcaPlan->endDate !== null) {
			$startTimestamp = $dcaPlan->startDate->getTimestamp();
			$endTimestamp = $dcaPlan->endDate->getTimestamp();
			$diffMonths = (int) round(($endTimestamp - $startTimestamp) / self::AvgSecondsPerMonth);
			$maxMonths = min($maxMonths, max(1, $diffMonths));
		}

		$dataPoints = [];
		for ($n = 1; $n <= $maxMonths; $n++) {
			$investedCapital = $startValue + $amount * $n;

			$projectedValue = $monthlyRate !== 0.0 ? $startValue * (1 + $monthlyRate) ** $n
					+ $amount * ((1 + $monthlyRate) ** $n - 1) / $monthlyRate : $investedCapital;

			$date = $dcaPlan->startDate->modify('+' . ($n - 1) . ' months');
			$dataPoints[] = new DcaPlanProjectionPointDto(
				id: $n,
				date: $date->format('Y-m'),
				investedCapital: CalculatorUtils::floatToDecimal($investedCapital, self::DecimalPrecision),
				projectedValue: CalculatorUtils::floatToDecimal($projectedValue, self::DecimalPrecision),
			);
		}

		return new DcaPlanProjectionDto(dataPoints: $dataPoints);
	}

	public function getProjectionWithSimulation(
		DcaPlan $dcaPlan,
		int $horizonYears,
		bool $withCurrentValue,
		int $simulations,
	): DcaPlanProjectionDto {
		$deterministic = $this->getProjection($dcaPlan, $horizonYears, $withCurrentValue);
		if ($simulations <= 0) {
			return $deterministic;
		}

		$tickerWeights = $this->getTickerWeights($dcaPlan);
		if (count($tickerWeights) === 0) {
			return $deterministic;
		}

		$monthlyReturns = $this->monteCarloSimulator->buildMonthlyCompositeReturns(
			$tickerWeights,
			new DateTimeImmutable('today'),
			self::HistoryYears,
			$this->buildProxyTickerIdMap($tickerWeights),
		);
		if (count($monthlyReturns) < self::MinHistoryMonthsForSimulation) {
			return $deterministic;
		}

		$months = count($deterministic->dataPoints);
		if ($months === 0) {
			return $deterministic;
		}

		// Anchor the bootstrap's central tendency to the (shrunk) deterministic projection. Without
		// this, the empirical sample's mean reflects the same trailing-CAGR bias the shrinkage exists
		// to fix — bands would float above the deterministic line.
		$returnRate = $this->calculateReturnRate($dcaPlan);
		$targetMonthlyMultiplier = 1.0 + $returnRate->monthly / 100.0;
		$adjustedReturns = $this->monteCarloSimulator->scaleMonthlyReturnsToMean($monthlyReturns, $targetMonthlyMultiplier);

		$result = $this->monteCarloSimulator->simulate(
			monthlyReturns: $adjustedReturns,
			startValue: $withCurrentValue ? $this->getCurrentValue($dcaPlan) : 0.0,
			amount: $dcaPlan->amount->toFloat(),
			months: $months,
			simulations: min($simulations, self::MaxSimulations),
			blockSize: self::BootstrapBlockSize,
		);

		$enriched = [];
		foreach ($deterministic->dataPoints as $i => $point) {
			$enriched[] = new DcaPlanProjectionPointDto(
				id: $point->id,
				date: $point->date,
				investedCapital: $point->investedCapital,
				projectedValue: $point->projectedValue,
				p10: CalculatorUtils::floatToDecimal($result->p10[$i], self::DecimalPrecision),
				p50: CalculatorUtils::floatToDecimal($result->p50[$i], self::DecimalPrecision),
				p90: CalculatorUtils::floatToDecimal($result->p90[$i], self::DecimalPrecision),
			);
		}

		return new DcaPlanProjectionDto(dataPoints: $enriched);
	}

	private function getCurrentValue(DcaPlan $dcaPlan): float
	{
		$openAssets = $this->assetWithPropertiesProvider->getAssetsWithAssetData(
			$dcaPlan->user,
			$dcaPlan->portfolio,
			new DateTimeImmutable('today'),
			AssetOrderEnum::TickerName,
		)->openAssets;

		return match ($dcaPlan->targetType) {
			DcaPlanTargetTypeEnum::Portfolio, DcaPlanTargetTypeEnum::Strategy =>
				array_sum(array_map(fn (AssetWithPropertiesDto $a) => $a->value->toFloat(), $openAssets)),
			DcaPlanTargetTypeEnum::Asset => $dcaPlan->asset !== null
				? array_sum(array_map(
					fn (AssetWithPropertiesDto $a) => $a->value->toFloat(),
					array_filter($openAssets, fn (AssetWithPropertiesDto $a) => $a->tickerId === $dcaPlan->asset->ticker->id),
				))
				: 0.0,
			DcaPlanTargetTypeEnum::Group => $dcaPlan->group !== null
				? array_sum(array_map(
					fn (AssetWithPropertiesDto $a) => $a->value->toFloat(),
					array_filter($openAssets, fn (AssetWithPropertiesDto $a) => $a->groupId === $dcaPlan->group->id),
				))
				: 0.0,
		};
	}

	/** @return list<TickerWeightDto> */
	private function getTickerWeights(DcaPlan $dcaPlan): array
	{
		return match ($dcaPlan->targetType) {
			DcaPlanTargetTypeEnum::Asset => $dcaPlan->asset !== null
				? [new TickerWeightDto($dcaPlan->asset->ticker->id, 1.0, $dcaPlan->asset->ticker->type)]
				: [],
			DcaPlanTargetTypeEnum::Group => $dcaPlan->group !== null
				? $this->getGroupTickerWeights($dcaPlan->group)
				: [],
			DcaPlanTargetTypeEnum::Strategy => $dcaPlan->strategy !== null
				? $this->getStrategyTickerWeights($dcaPlan->strategy)
				: [],
			DcaPlanTargetTypeEnum::Portfolio => $this->getPortfolioTickerWeights($dcaPlan->portfolio, $dcaPlan->user),
		};
	}

	/**
	 * Resolves a per-ticker proxy ticker id by looking up the admin-configured proxy for the ticker's
	 * type. Returns null for tickers whose type has no proxy configured (or is unknown), and never
	 * proxies a ticker to itself.
	 *
	 * @param list<TickerWeightDto> $tickerWeights
	 * @return array<int, int|null>
	 */
	private function buildProxyTickerIdMap(array $tickerWeights): array
	{
		$proxyMap = [];
		/** @var array<string, int|null> $proxyByType */
		$proxyByType = [];
		foreach ($tickerWeights as $tickerWeight) {
			if ($tickerWeight->type === null) {
				$proxyMap[$tickerWeight->tickerId] = null;
				continue;
			}

			$typeKey = $tickerWeight->type->value;
			if (!array_key_exists($typeKey, $proxyByType)) {
				$proxyAsset = $this->proxyAssetProvider->getProxyAssetByTickerType($tickerWeight->type);
				$proxyByType[$typeKey] = $proxyAsset?->ticker->id;
			}

			$proxyTickerId = $proxyByType[$typeKey];
			$proxyMap[$tickerWeight->tickerId] = $proxyTickerId === $tickerWeight->tickerId ? null : $proxyTickerId;
		}

		return $proxyMap;
	}

	/**
	 * All assets in the group carry equal weight.
	 *
	 * @return list<TickerWeightDto>
	 */
	private function getGroupTickerWeights(Group $group): array
	{
		$tickerWeights = [];
		foreach ($group->assets as $asset) {
			$tickerWeights[$asset->ticker->id] = new TickerWeightDto($asset->ticker->id, 1.0, $asset->ticker->type);
		}

		return array_values($tickerWeights);
	}

	/**
	 * Each strategy item contributes its defined percentage as the weight.
	 * When a strategy item targets a Group, its percentage is split equally among the group's assets.
	 *
	 * @return list<TickerWeightDto>
	 */
	private function getStrategyTickerWeights(Strategy $strategy): array
	{
		/** @var array<int, float> $tickerWeights */
		$tickerWeights = [];
		/** @var array<int, TickerTypeEnum> $tickerTypes */
		$tickerTypes = [];
		foreach ($strategy->strategyItems as $strategyItem) {
			$weight = $strategyItem->percentage->toFloat();

			if ($strategyItem->asset !== null) {
				$tickerId = $strategyItem->asset->ticker->id;
				$tickerWeights[$tickerId] = ($tickerWeights[$tickerId] ?? 0.0) + $weight;
				$tickerTypes[$tickerId] = $strategyItem->asset->ticker->type;
			} elseif ($strategyItem->group !== null) {
				$groupAssets = iterator_to_array($strategyItem->group->assets, false);
				$groupAssetCount = count($groupAssets);
				if ($groupAssetCount === 0) {
					continue;
				}

				$assetWeight = $weight / $groupAssetCount;
				foreach ($groupAssets as $asset) {
					$tickerId = $asset->ticker->id;
					$tickerWeights[$tickerId] = ($tickerWeights[$tickerId] ?? 0.0) + $assetWeight;
					$tickerTypes[$tickerId] = $asset->ticker->type;
				}
			}
		}

		return array_map(
			fn (int $tickerId, float $weight) => new TickerWeightDto($tickerId, $weight, $tickerTypes[$tickerId] ?? null),
			array_keys($tickerWeights),
			$tickerWeights,
		);
	}

	/**
	 * Assets are weighted by their current percentage of the portfolio value.
	 *
	 * @return list<TickerWeightDto>
	 */
	private function getPortfolioTickerWeights(Portfolio $portfolio, User $user): array
	{
		$assetsWithProperties = $this->assetWithPropertiesProvider->getAssetsWithAssetData(
			$user,
			$portfolio,
			new DateTimeImmutable('today'),
			AssetOrderEnum::TickerName,
		);

		$tickerWeights = [];
		foreach ($assetsWithProperties->openAssets as $assetDto) {
			$tickerWeights[$assetDto->tickerId] = new TickerWeightDto($assetDto->tickerId, $assetDto->percentage, $assetDto->ticker->type);
		}

		return array_values($tickerWeights);
	}

	private function calculateTickerReturnRate(int $tickerId): ?float
	{
		$toDate = new DateTimeImmutable('today');
		$fromDate = $toDate->sub(new DateInterval('P' . self::HistoryYears . 'Y'));

		$firstData = $this->tickerDataProvider->getFirstTickerData($tickerId, $fromDate);
		$lastData = $this->tickerDataProvider->getLastTickerData($tickerId, $toDate);

		if ($firstData === null || $lastData === null || $firstData->date >= $lastData->date || $firstData->close->isZero()) {
			return null;
		}

		$days = (int) $lastData->date->diff($firstData->date)->days;
		$years = max($days, self::MinDays) / 365.0;
		$cagr = (($lastData->close->toFloat() / $firstData->close->toFloat()) ** (1.0 / $years) - 1.0) * 100.0;

		return CalculatorUtils::roundPercentage($cagr);
	}
}
