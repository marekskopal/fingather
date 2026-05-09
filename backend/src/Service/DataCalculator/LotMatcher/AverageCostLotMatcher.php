<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\LotMatcher;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Service\DataCalculator\Dto\FifoMatchDto;
use FinGather\Service\DataCalculator\Dto\TransactionBuyDto;
use FinGather\Utils\CalculatorUtils;

final readonly class AverageCostLotMatcher implements LotMatcherInterface
{
	public function consumeLots(
		array &$buys,
		?int $brokerId,
		DateTimeImmutable $sellDate,
		Decimal $sellUnitsAbs,
		array $splits,
	): array {
		$totalSplitAdjustedUnits = new Decimal(0, 18);
		$totalCostDefault = new Decimal(0, 18);
		$totalCostTicker = new Decimal(0, 18);
		$earliestDate = null;

		foreach ($buys as $buy) {
			if ($buy->brokerId !== $brokerId) {
				continue;
			}

			$splitFactor = CalculatorUtils::countSplitFactor($buy->actionCreated, $sellDate, $splits);
			$splitAdjustedUnits = $buy->units->mul($splitFactor);

			$totalSplitAdjustedUnits = $totalSplitAdjustedUnits->add($splitAdjustedUnits);
			$totalCostDefault = $totalCostDefault->add($buy->units->mul($buy->priceDefaultCurrency));
			$totalCostTicker = $totalCostTicker->add($buy->units->mul($buy->priceTickerCurrency));

			if ($earliestDate === null || $buy->actionCreated < $earliestDate) {
				$earliestDate = $buy->actionCreated;
			}
		}

		if ($totalSplitAdjustedUnits->isZero() || $earliestDate === null) {
			return [];
		}

		$usedUnits = $sellUnitsAbs <= $totalSplitAdjustedUnits ? $sellUnitsAbs : $totalSplitAdjustedUnits;

		$avgPriceDefault = $totalCostDefault->div($totalSplitAdjustedUnits);
		$avgPriceTicker = $totalCostTicker->div($totalSplitAdjustedUnits);

		$syntheticBuy = new TransactionBuyDto(
			brokerId: $brokerId,
			actionCreated: $earliestDate,
			units: $usedUnits,
			priceTickerCurrency: $avgPriceTicker,
			priceDefaultCurrency: $avgPriceDefault,
			priceWithSplitTickerCurrency: $avgPriceTicker,
			priceWithSplitDefaultCurrency: $avgPriceDefault,
		);

		$consumeRatio = $usedUnits->div($totalSplitAdjustedUnits);

		foreach ($buys as $buyKey => $buy) {
			if ($buy->brokerId !== $brokerId) {
				continue;
			}

			$newUnits = $buy->units->sub($buy->units->mul($consumeRatio));

			if ($newUnits->isZero() || $newUnits < new Decimal('0.00000001', 18)) {
				unset($buys[$buyKey]);
				continue;
			}

			$buys[$buyKey]->units = $newUnits;
		}

		return [new FifoMatchDto(
			buy: $syntheticBuy,
			usedUnitsWithSplits: $usedUnits,
			usedOriginalUnits: $usedUnits,
		)];
	}
}
