<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Dto\AssetWithPropertiesDto;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use FinGather\Utils\CalculatorUtils;

class DataCalculator
{
	/** @param array<int, AssetWithPropertiesDto> $assets */
	public function calculate(
		array $assets,
		DateTimeImmutable $dateTime,
		DateTimeImmutable $firstTransactionActionCreated,
	): CalculatedDataDto
	{
		$fromFirstTransactionDays = (int) $dateTime->diff($firstTransactionActionCreated)->days;

		$sumAssetValue = new Decimal(0);
		$sumAssetTransactionValue = new Decimal(0);
		$sumAssetTransactionValueDefaultCurrency = new Decimal(0);
		$sumGain = new Decimal(0);
		$sumGainDefaultCurrency = new Decimal(0);
		$sumDividendGain = new Decimal(0);
		$sumFxImpact = new Decimal(0);

		foreach ($assets as $asset) {
			$sumAssetValue = $sumAssetValue->add($asset->value);
			$sumAssetTransactionValue = $sumAssetTransactionValue->add($asset->transactionValue);
			$sumAssetTransactionValueDefaultCurrency = $sumAssetTransactionValueDefaultCurrency->add(
				$asset->transactionValueDefaultCurrency,
			);
			$sumGain = $sumGain->add($asset->gain);
			$sumGainDefaultCurrency = $sumGainDefaultCurrency->add($asset->gainDefaultCurrency);
			$sumDividendGain = $sumDividendGain->add($asset->dividendGain);
			$sumFxImpact = $sumFxImpact->add($asset->fxImpact);
		}

		$gainPercentage = CalculatorUtils::toPercentage($sumGain, $sumAssetTransactionValue);
		$gainPercentagePerAnnum = CalculatorUtils::toPercentagePerAnnum($gainPercentage, $fromFirstTransactionDays);
		$dividendGainPercentage = CalculatorUtils::toPercentage($sumDividendGain, $sumAssetTransactionValueDefaultCurrency);
		$dividendGainPercentagePerAnnum = CalculatorUtils::toPercentagePerAnnum($dividendGainPercentage, $fromFirstTransactionDays);
		$fxImpactPercentage = CalculatorUtils::toPercentage($sumFxImpact, $sumAssetTransactionValueDefaultCurrency);
		$fxImpactPercentagePerAnnum = CalculatorUtils::toPercentagePerAnnum($fxImpactPercentage, $fromFirstTransactionDays);

		return new CalculatedDataDto(
			value: $sumAssetValue,
			transactionValue: $sumAssetTransactionValueDefaultCurrency,
			gain: $sumGainDefaultCurrency,
			gainPercentage: $gainPercentage,
			gainPercentagePerAnnum: $gainPercentagePerAnnum,
			dividendGain: $sumDividendGain,
			dividendGainPercentage: $dividendGainPercentage,
			dividendGainPercentagePerAnnum: $dividendGainPercentagePerAnnum,
			fxImpact: $sumFxImpact,
			fxImpactPercentage: $fxImpactPercentage,
			fxImpactPercentagePerAnnum: $fxImpactPercentagePerAnnum,
			return: $sumGainDefaultCurrency->add($sumDividendGain)->add($sumFxImpact),
			returnPercentage: round($gainPercentage + $dividendGainPercentage + $fxImpactPercentage, 2),
			returnPercentagePerAnnum: round($gainPercentagePerAnnum + $dividendGainPercentagePerAnnum + $fxImpactPercentagePerAnnum, 2),
		);
	}
}
