<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use FinGather\Dto\PortfolioDataDto;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\YearCalculatedDataDto;
use FinGather\Service\Provider\PortfolioDataProvider;
use FinGather\Service\Provider\TransactionProvider;
use FinGather\Utils\CalculatorUtils;
use Safe\DateTimeImmutable;

class OverviewDataCalculator
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

		$fromDate = $firstTransaction->getActionCreated();
		$toDate = new DateTimeImmutable('today');

		$fromDateYear = (int) $fromDate->format('Y');
		$toDateYear = (int) $toDate->format('Y');

		$yearCalculatedData = [];

		for ($i = $fromDateYear; $i <= $toDateYear; $i++) {
			$yearFromDate = (new DateTimeImmutable('first day of january ' . $i))->setTime(0, 0);
			$yearToDate = $i === $toDateYear ? $toDate : (new DateTimeImmutable('last day of december ' . $i))->setTime(0, 0);

			$portfolioDataFromDate = PortfolioDataDto::fromEntity(
				$this->portfolioDataProvider->getPortfolioData($user, $portfolio, $yearFromDate),
			);
			$portfolioDataToDate = PortfolioDataDto::fromEntity(
				$this->portfolioDataProvider->getPortfolioData($user, $portfolio, $yearToDate),
			);

			$fromFirstTransactionDays = (int) $yearToDate->diff($firstTransaction->getActionCreated())->days;

			if ($i === $fromDateYear) {
				$yearCalculatedData[$i] = new YearCalculatedDataDto(
					year: $i,
					value: $portfolioDataToDate->value,
					transactionValue: $portfolioDataToDate->transactionValue,
					gain: $portfolioDataToDate->gain,
					gainPercentage: $portfolioDataToDate->gainPercentage,
					gainPercentagePerAnnum: $portfolioDataToDate->gainPercentagePerAnnum,
					dividendGain: $portfolioDataToDate->dividendGain,
					dividendGainPercentage: $portfolioDataToDate->dividendGainPercentage,
					dividendGainPercentagePerAnnum: $portfolioDataToDate->dividendGainPercentagePerAnnum,
					fxImpact: $portfolioDataToDate->fxImpact,
					fxImpactPercentage: $portfolioDataToDate->fxImpactPercentage,
					fxImpactPercentagePerAnnum: $portfolioDataToDate->fxImpactPercentagePerAnnum,
					return: $portfolioDataToDate->return,
					returnPercentage: $portfolioDataToDate->returnPercentage,
					returnPercentagePerAnnum: $portfolioDataToDate->returnPercentagePerAnnum,
				);
				continue;
			}

			$investSum = $portfolioDataFromDate->value->add(
				$portfolioDataToDate->transactionValue->sub($portfolioDataFromDate->transactionValue),
			);

			$transactionValue = $portfolioDataToDate->transactionValue->sub($portfolioDataFromDate->transactionValue);
			$gain = $portfolioDataToDate->gain->sub($portfolioDataFromDate->gain);
			$dividendGain = $portfolioDataToDate->dividendGain->sub($portfolioDataFromDate->dividendGain);
			$fxImpact = $portfolioDataToDate->fxImpact->sub($portfolioDataFromDate->fxImpact);
			$return = $portfolioDataToDate->return->sub($portfolioDataFromDate->return);

			$gainPercentage = CalculatorUtils::toPercentage($gain, $investSum);
			$gainPercentagePerAnnum = CalculatorUtils::toPercentagePerAnnum($gainPercentage, $fromFirstTransactionDays);
			$dividendGainPercentage = CalculatorUtils::toPercentage($dividendGain, $investSum);
			$dividendGainPercentagePerAnnum = CalculatorUtils::toPercentagePerAnnum($dividendGainPercentage, $fromFirstTransactionDays);
			$fxImpactPercentage = CalculatorUtils::toPercentage($fxImpact, $investSum);
			$fxImpactPercentagePerAnnum = CalculatorUtils::toPercentagePerAnnum($fxImpactPercentage, $fromFirstTransactionDays);

			$yearCalculatedData[$i] = new YearCalculatedDataDto(
				year: $i,
				value: $portfolioDataToDate->value,
				transactionValue: $transactionValue,
				gain: $gain,
				gainPercentage: $gainPercentage,
				gainPercentagePerAnnum: $gainPercentagePerAnnum,
				dividendGain: $dividendGain,
				dividendGainPercentage: $dividendGainPercentage,
				dividendGainPercentagePerAnnum: $dividendGainPercentagePerAnnum,
				fxImpact: $fxImpact,
				fxImpactPercentage: $fxImpactPercentage,
				fxImpactPercentagePerAnnum: $fxImpactPercentagePerAnnum,
				return: $return,
				returnPercentage: round($gainPercentage + $dividendGainPercentage + $fxImpactPercentage, 2),
				returnPercentagePerAnnum: round($gainPercentagePerAnnum + $dividendGainPercentagePerAnnum + $fxImpactPercentagePerAnnum, 2),
			);
		}

		return $yearCalculatedData;
	}
}
