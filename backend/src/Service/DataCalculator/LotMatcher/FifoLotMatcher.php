<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\LotMatcher;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Service\DataCalculator\Dto\FifoMatchDto;
use FinGather\Service\DataCalculator\Dto\TransactionBuyDto;
use FinGather\Utils\CalculatorUtils;

final readonly class FifoLotMatcher implements LotMatcherInterface
{
	/** @return list<FifoMatchDto> */
	public function consumeLots(array &$buys, ?int $brokerId, DateTimeImmutable $sellDate, Decimal $sellUnitsAbs, array $splits,): array
	{
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
