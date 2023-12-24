<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use Decimal\Decimal;
use FinGather\Dto\AssetDto;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use Safe\DateTimeImmutable;

class DataCalculator
{
	/** @param array<int, AssetDto> $assets */
	public function calculate(User $user, DateTimeImmutable $dateTime, array $assets): CalculatedDataDto
	{
		$sumAssetValue = new Decimal(0);
		$sumAssetTransactionValue = new Decimal(0);
		$sumDividendGain = new Decimal(0);
		$sumFxImpact = new Decimal(0);

		foreach ($assets as $asset) {
			$sumAssetValue = $sumAssetValue->add($asset->value);
			$sumAssetTransactionValue = $sumAssetTransactionValue->add($asset->transactionValue);
			$sumDividendGain = $sumDividendGain->add($asset->dividendGain);
			$sumFxImpact = $sumFxImpact->add($asset->fxImpact);
		}

		//TODO: count performance

		$gain = $sumAssetValue->sub($sumAssetTransactionValue);

		$gainPercentage = 0.0;
		$dividendGainPercentage = 0.0;
		$fxImpactPercentage = 0.0;
		//is greater then 0
		if ($sumAssetTransactionValue->compareTo(0) === 1) {
			$gainPercentage = round($gain->div($sumAssetTransactionValue)->mul(100)->toFloat(), 2);
			$fxImpactPercentage = round($sumFxImpact->div($sumAssetTransactionValue)->mul(100)->toFloat(), 2);

			//is greater then 0
			if ($sumDividendGain->compareTo(0) === 1) {
				$dividendGainPercentage = round($sumDividendGain->div($sumAssetTransactionValue)->mul(100)->toFloat(), 2);
			}
		}

		return new CalculatedDataDto(
			value: $sumAssetValue,
			transactionValue: $sumAssetTransactionValue,
			gain: $sumAssetValue->sub($sumAssetTransactionValue),
			gainPercentage: $gainPercentage,
			dividendGain: $sumDividendGain,
			dividendGainPercentage: $dividendGainPercentage,
			fxImpact: $sumFxImpact,
			fxImpactPercentage: $fxImpactPercentage,
			return: $gain->add($sumDividendGain)->add($sumFxImpact),
			returnPercentage: round($gainPercentage + $dividendGainPercentage + $fxImpactPercentage, 2),
			performance: 0.0,
		);
	}
}
