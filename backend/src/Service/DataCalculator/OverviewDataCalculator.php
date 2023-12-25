<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use FinGather\Dto\PortfolioDataDto;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\YearCalculatedDataDto;
use FinGather\Service\Provider\PortfolioDataProvider;
use FinGather\Service\Provider\TransactionProvider;
use Safe\DateTimeImmutable;

class OverviewDataCalculator
{
	public function __construct(
		private readonly TransactionProvider $transactionProvider,
		private readonly PortfolioDataProvider $portfolioDataProvider,
	) {
	}

	/** @return array<int,YearCalculatedDataDto> */
	public function yearCalculate(User $user): array
	{
		$firstTransaction = $this->transactionProvider->getFirstTransaction($user);
		if ($firstTransaction === null) {
			return [];
		}

		$fromDate = $firstTransaction->getCreated();
		$toDate = new DateTimeImmutable('today');

		$fromDateYear = (int) $fromDate->format('Y');
		$toDateYear = (int) $toDate->format('Y');

		$yearCalculatedData = [];

		for ($i = $fromDateYear; $i <= $toDateYear; $i++) {
			$yearFromDate = (new DateTimeImmutable('first day of january ' . $i))->setTime(0, 0);
			$yearToDate = (new DateTimeImmutable('last day of december ' . $i))->setTime(0, 0);

			$portfolioDataFromDate = PortfolioDataDto::fromEntity($this->portfolioDataProvider->getPortfolioData($user, $yearFromDate));
			$portfolioDataToDate = PortfolioDataDto::fromEntity($this->portfolioDataProvider->getPortfolioData($user, $yearToDate));

			if ($i === $fromDateYear) {
				$yearCalculatedData[$i] = new YearCalculatedDataDto(
					year: $i,
					value: $portfolioDataToDate->value,
					transactionValue: $portfolioDataToDate->transactionValue,
					gain: $portfolioDataToDate->gain,
					gainPercentage: $portfolioDataToDate->gainPercentage,
					dividendGain: $portfolioDataToDate->dividendGain,
					dividendGainPercentage: $portfolioDataToDate->dividendGainPercentage,
					fxImpact: $portfolioDataToDate->fxImpact,
					fxImpactPercentage: $portfolioDataToDate->fxImpactPercentage,
					return: $portfolioDataToDate->return,
					returnPercentage: $portfolioDataToDate->returnPercentage,
					performance: 0.0,
				);
				continue;
			}

			$investSum = $portfolioDataFromDate->value->add(
				$portfolioDataToDate->transactionValue->sub($portfolioDataFromDate->transactionValue)
			);

			$transactionValue = $portfolioDataToDate->transactionValue->sub($portfolioDataFromDate->transactionValue);
			$gain = $portfolioDataToDate->gain->sub($portfolioDataFromDate->gain);
			$dividendGain = $portfolioDataToDate->dividendGain->sub($portfolioDataFromDate->dividendGain);
			$fxImpact = $portfolioDataToDate->fxImpact->sub($portfolioDataFromDate->fxImpact);
			$return = $portfolioDataToDate->return->sub($portfolioDataFromDate->return);

			$gainPercentage = round($gain->div($investSum)->mul(100)->toFloat(), 2);
			$dividendGainPercentage = round($dividendGain->div($investSum)->mul(100)->toFloat(), 2);
			$fxImpactPercentage = round($fxImpact->div($investSum)->mul(100)->toFloat(), 2);

			$yearCalculatedData[$i] = new YearCalculatedDataDto(
				year: $i,
				value: $portfolioDataToDate->value,
				transactionValue: $transactionValue,
				gain: $gain,
				gainPercentage: $gainPercentage,
				dividendGain: $dividendGain,
				dividendGainPercentage: $dividendGainPercentage,
				fxImpact: $fxImpact,
				fxImpactPercentage: $fxImpactPercentage,
				return: $return,
				returnPercentage: round($gainPercentage + $dividendGainPercentage + $fxImpactPercentage, 2),
				performance: 0.0,
			);
		}

		return $yearCalculatedData;
	}
}
