<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\AssetData;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use FinGather\Utils\CalculatorUtils;

final class DataCalculator
{
	/** @param array<int, AssetData> $assets */
	public function calculate(
		array $assets,
		DateTimeImmutable $dateTime,
		DateTimeImmutable $firstTransactionActionCreated,
	): CalculatedDataDto {
		$fromFirstTransactionDays = (int) $dateTime->diff($firstTransactionActionCreated)->days;

		$sumAssetValue = new Decimal(0);
		$sumAssetTransactionValueDefaultCurrency = new Decimal(0);
		$sumGainDefaultCurrency = new Decimal(0);
		$sumRealizedGainDefaultCurrency = new Decimal(0);
		$sumDividendGain = new Decimal(0);
		$sumFxImpact = new Decimal(0);
		$sumTax = new Decimal(0);
		$sumFee = new Decimal(0);

		foreach ($assets as $asset) {
			$sumRealizedGainDefaultCurrency = $sumRealizedGainDefaultCurrency->add($asset->realizedGainDefaultCurrency);
			$sumDividendGain = $sumDividendGain->add($asset->dividendGainDefaultCurrency);
			$sumTax = $sumTax->add($asset->taxDefaultCurrency);
			$sumFee = $sumFee->add($asset->feeDefaultCurrency);
			$sumAssetValue = $sumAssetValue->add($asset->value);
			$sumAssetTransactionValueDefaultCurrency = $sumAssetTransactionValueDefaultCurrency->add(
				$asset->transactionValueDefaultCurrency,
			);
			$sumGainDefaultCurrency = $sumGainDefaultCurrency->add($asset->gainDefaultCurrency);
			$sumFxImpact = $sumFxImpact->add($asset->fxImpact);
		}

		$gainPercentage = CalculatorUtils::toPercentage($sumGainDefaultCurrency, $sumAssetTransactionValueDefaultCurrency);
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
			tax: $sumTax,
			fee: $sumFee,
			realizedGain: $sumRealizedGainDefaultCurrency,
		);
	}
}
