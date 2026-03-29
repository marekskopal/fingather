<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Service\DataCalculator\Dto\FifoMatchDto;
use FinGather\Service\DataCalculator\Dto\TransactionBuyDto;
use FinGather\Service\Provider\Dto\SplitDto;
use FinGather\Utils\CalculatorUtils;

final readonly class FifoLotMatcher
{
	/**
	 * Consumes buy lots in FIFO order for a given broker, adjusting the $buys array in place.
	 * Returns the matched lot portions with their used units.
	 *
	 * @param array<int, TransactionBuyDto> $buys Modified in place: consumed lots are removed, partial lots are adjusted
	 * @param list<SplitDto> $splits
	 * @return list<FifoMatchDto>
	 */
	public static function consumeLots(
		array &$buys,
		int|null $brokerId,
		DateTimeImmutable $sellDate,
		Decimal $sellUnitsAbs,
		array $splits,
	): array {
		$matches = [];
		$sumBuyUnits = new Decimal(0, 18);

		$buysForBroker = array_filter($buys, fn(TransactionBuyDto $buy) => $buy->brokerId === $brokerId);

		foreach ($buysForBroker as $buyKey => $buy) {
			$buySplitFactor = CalculatorUtils::countSplitFactor($buy->actionCreated, $sellDate, $splits);
			$buyUnitsWithSplits = $buy->units->mul($buySplitFactor);

			$remainingSellUnits = $sellUnitsAbs->sub($sumBuyUnits);
			$usedUnitsWithSplits = $buyUnitsWithSplits <= $remainingSellUnits ? $buyUnitsWithSplits : $remainingSellUnits;
			$usedOriginalUnits = $usedUnitsWithSplits->div($buySplitFactor);

			$matches[] = new FifoMatchDto(buy: $buy, usedUnitsWithSplits: $usedUnitsWithSplits, usedOriginalUnits: $usedOriginalUnits);

			$sumBuyUnits = $sumBuyUnits->add($buyUnitsWithSplits);

			if ($sumBuyUnits <= $sellUnitsAbs) {
				unset($buys[$buyKey]);
			} else {
				$buys[$buyKey]->units = $sumBuyUnits->sub($sellUnitsAbs)->div($buySplitFactor);
			}

			if ($sumBuyUnits >= $sellUnitsAbs) {
				break;
			}
		}

		return $matches;
	}
}
