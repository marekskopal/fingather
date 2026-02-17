<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use DateTimeImmutable;
use FinGather\Dto\PortfolioDataDto;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\YearCalculatedDataDto;
use FinGather\Service\Provider\PortfolioDataProvider;
use FinGather\Service\Provider\TransactionProvider;
use FinGather\Utils\CalculatorUtils;

final class OverviewDataCalculator
{
	public function __construct(
		private readonly TransactionProvider $transactionProvider,
		private readonly PortfolioDataProvider $portfolioDataProvider,
	) {
	}

	/** @return array<int,YearCalculatedDataDto> */
	public function yearCalculate(User $user, Portfolio $portfolio): array
	{
		$firstTransaction = $this->transactionProvider->getFirstTransaction($user, $portfolio);
		if ($firstTransaction === null) {
			return [];
		}

		$fromDate = $firstTransaction->actionCreated;
		$toDate = new DateTimeImmutable('today');

		$fromDateYear = (int) $fromDate->format('Y');
		$toDateYear = (int) $toDate->format('Y');

		$yearCalculatedData = [];

		$yearFromDate = null;

		for ($i = $fromDateYear; $i <= $toDateYear; $i++) {
			$yearToDate = $i === $toDateYear ? $toDate : (new DateTimeImmutable('last day of december ' . $i))->setTime(0, 0);

			$portfolioDataFromDate = null;
			if ($yearFromDate !== null) {
				$portfolioDataFromDate = PortfolioDataDto::fromCalculatedDataDto(
					$this->portfolioDataProvider->getPortfolioData($user, $portfolio, $yearFromDate),
				);
			}
			$portfolioDataToDate = PortfolioDataDto::fromCalculatedDataDto(
				$this->portfolioDataProvider->getPortfolioData($user, $portfolio, $yearToDate),
			);
			$yearFromDate = $yearToDate;

			$fromFirstTransactionDays = (int) $yearToDate->diff($firstTransaction->actionCreated)->days;

			if ($i === $fromDateYear) {
				$yearCalculatedData[$i] = new YearCalculatedDataDto(
					year: $i,
					value: $portfolioDataToDate->value,
					valueInterannually: null,
					transactionValue: $portfolioDataToDate->transactionValue,
					transactionValueInterannually: null,
					gain: $portfolioDataToDate->gain,
					gainInterannually: null,
					gainPercentage: $portfolioDataToDate->gainPercentage,
					gainPercentageInterannually: null,
					gainPercentagePerAnnum: $portfolioDataToDate->gainPercentagePerAnnum,
					gainPercentagePerAnnumInterannually: null,
					realizedGain: $portfolioDataToDate->realizedGain,
					realizedGainInterannually: null,
					dividendYield: $portfolioDataToDate->dividendYield,
					dividendYieldInterannually: null,
					dividendYieldPercentage: $portfolioDataToDate->dividendYieldPercentage,
					dividendYieldPercentageInterannually: null,
					dividendYieldPercentagePerAnnum: $portfolioDataToDate->dividendYieldPercentagePerAnnum,
					dividendYieldPercentagePerAnnumInterannually: null,
					fxImpact: $portfolioDataToDate->fxImpact,
					fxImpactInterannually: null,
					fxImpactPercentage: $portfolioDataToDate->fxImpactPercentage,
					fxImpactPercentageInterannually: null,
					fxImpactPercentagePerAnnum: $portfolioDataToDate->fxImpactPercentagePerAnnum,
					fxImpactPercentagePerAnnumInterannually: null,
					return: $portfolioDataToDate->return,
					returnInterannually: null,
					returnPercentage: $portfolioDataToDate->returnPercentage,
					returnPercentageInterannually: null,
					returnPercentagePerAnnum: $portfolioDataToDate->returnPercentagePerAnnum,
					returnPercentagePerAnnumInterannually: null,
					tax: $portfolioDataToDate->tax,
					taxInterannually: null,
					fee: $portfolioDataToDate->fee,
					feeInterannually: null,
				);
				continue;
			}

			if ($portfolioDataFromDate === null) {
				continue;
			}

			$value = $portfolioDataToDate->value->sub($portfolioDataFromDate->value);
			$transactionValue = $portfolioDataToDate->transactionValue->sub($portfolioDataFromDate->transactionValue);
			$gain = $portfolioDataToDate->gain->sub($portfolioDataFromDate->gain);
			$realizedGain = $portfolioDataToDate->realizedGain->sub($portfolioDataFromDate->realizedGain);
			$dividendYield = $portfolioDataToDate->dividendYield->sub($portfolioDataFromDate->dividendYield);
			$fxImpact = $portfolioDataToDate->fxImpact->sub($portfolioDataFromDate->fxImpact);
			$return = $portfolioDataToDate->return->sub($portfolioDataFromDate->return);
			$tax = $portfolioDataToDate->tax->sub($portfolioDataFromDate->tax);
			$fee = $portfolioDataToDate->fee->sub($portfolioDataFromDate->fee);

			$gainPercentage = CalculatorUtils::diffToPercentage($portfolioDataFromDate->gain, $portfolioDataToDate->gain);
			$gainPercentagePerAnnum = CalculatorUtils::toPercentagePerAnnum($gainPercentage, $fromFirstTransactionDays);
			$dividendYieldPercentage = CalculatorUtils::diffToPercentage(
				$portfolioDataFromDate->dividendYield,
				$portfolioDataToDate->dividendYield,
			);
			$dividendYieldPercentagePerAnnum = CalculatorUtils::toPercentagePerAnnum($dividendYieldPercentage, $fromFirstTransactionDays);
			$fxImpactPercentage = CalculatorUtils::diffToPercentage($portfolioDataFromDate->fxImpact, $portfolioDataToDate->fxImpact);
			$fxImpactPercentagePerAnnum = CalculatorUtils::toPercentagePerAnnum($fxImpactPercentage, $fromFirstTransactionDays);
			$returnPercentage = CalculatorUtils::diffToPercentage($portfolioDataFromDate->return, $portfolioDataToDate->return);
			$returnPercentagePerAnnum = CalculatorUtils::toPercentagePerAnnum($returnPercentage, $fromFirstTransactionDays);

			$yearCalculatedData[$i] = new YearCalculatedDataDto(
				year: $i,
				value: $portfolioDataToDate->value,
				valueInterannually: $value,
				transactionValue: $portfolioDataToDate->transactionValue,
				transactionValueInterannually: $transactionValue,
				gain: $portfolioDataToDate->gain,
				gainInterannually: $gain,
				gainPercentage: $portfolioDataToDate->gainPercentage,
				gainPercentageInterannually: $gainPercentage,
				gainPercentagePerAnnum: $portfolioDataToDate->gainPercentagePerAnnum,
				gainPercentagePerAnnumInterannually: $gainPercentagePerAnnum,
				realizedGain: $portfolioDataToDate->realizedGain,
				realizedGainInterannually: $realizedGain,
				dividendYield: $portfolioDataToDate->dividendYield,
				dividendYieldInterannually: $dividendYield,
				dividendYieldPercentage: $portfolioDataToDate->dividendYieldPercentage,
				dividendYieldPercentageInterannually: $dividendYieldPercentage,
				dividendYieldPercentagePerAnnum: $portfolioDataToDate->dividendYieldPercentagePerAnnum,
				dividendYieldPercentagePerAnnumInterannually: $dividendYieldPercentagePerAnnum,
				fxImpact: $portfolioDataToDate->fxImpact,
				fxImpactInterannually: $fxImpact,
				fxImpactPercentage: $portfolioDataToDate->fxImpactPercentage,
				fxImpactPercentageInterannually: $fxImpactPercentage,
				fxImpactPercentagePerAnnum: $portfolioDataToDate->fxImpactPercentagePerAnnum,
				fxImpactPercentagePerAnnumInterannually: $fxImpactPercentagePerAnnum,
				return: $portfolioDataToDate->return,
				returnInterannually: $return,
				returnPercentage: $portfolioDataToDate->returnPercentage,
				returnPercentageInterannually: $returnPercentage,
				returnPercentagePerAnnum: $portfolioDataToDate->returnPercentagePerAnnum,
				returnPercentagePerAnnumInterannually: $returnPercentagePerAnnum,
				tax: $portfolioDataToDate->tax,
				taxInterannually: $tax,
				fee: $portfolioDataToDate->fee,
				feeInterannually: $fee,
			);
		}

		return $yearCalculatedData;
	}
}
