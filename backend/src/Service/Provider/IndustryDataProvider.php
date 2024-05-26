<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use Cycle\Database\Exception\StatementException\ConstrainException;
use DateTimeImmutable;
use FinGather\Model\Entity\Industry;
use FinGather\Model\Entity\IndustryData;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\IndustryDataRepository;
use FinGather\Utils\DateTimeUtils;

class IndustryDataProvider
{
	public function __construct(
		private readonly IndustryDataRepository $industryDataRepository,
		private readonly CalculatedDataProvider $calculatedDataProvider,
	) {
	}

	public function getIndustryData(Industry $industry, User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): IndustryData
	{
		$dateTime = DateTimeUtils::setEndOfDateTime($dateTime);

		$industryData = $this->industryDataRepository->findIndustryData($industry->getId(), $portfolio->getId(), $dateTime);
		if ($industryData !== null) {
			return $industryData;
		}

		$calculatedData = $this->calculatedDataProvider->getCalculatedDate($user, $portfolio, $dateTime, industry: $industry);

		$industryData = new IndustryData(
			industry: $industry,
			user: $user,
			portfolio: $portfolio,
			date: $dateTime,
			value: $calculatedData->value,
			transactionValue: $calculatedData->transactionValue,
			gain: $calculatedData->gain,
			gainPercentage: $calculatedData->gainPercentage,
			gainPercentagePerAnnum: $calculatedData->gainPercentagePerAnnum,
			realizedGain: $calculatedData->realizedGain,
			dividendGain: $calculatedData->dividendGain,
			dividendGainPercentage: $calculatedData->dividendGainPercentage,
			dividendGainPercentagePerAnnum: $calculatedData->dividendGainPercentagePerAnnum,
			fxImpact: $calculatedData->fxImpact,
			fxImpactPercentage: $calculatedData->fxImpactPercentage,
			fxImpactPercentagePerAnnum: $calculatedData->fxImpactPercentagePerAnnum,
			return: $calculatedData->return,
			returnPercentage: $calculatedData->returnPercentage,
			returnPercentagePerAnnum: $calculatedData->returnPercentagePerAnnum,
			tax: $calculatedData->tax,
			fee: $calculatedData->fee,
		);

		try {
			$this->industryDataRepository->persist($industryData);
		} catch (ConstrainException) {
			$industryData = $this->industryDataRepository->findIndustryData($industry->getId(), $industry->getId(), $dateTime);
			assert($industryData instanceof IndustryData);
		}

		return $industryData;
	}

	public function deleteUserIndustryData(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $date = null): void
	{
		$this->industryDataRepository->deleteUserIndustryData($user?->getId(), $portfolio?->getId(), $date);
	}
}
