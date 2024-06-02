<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use Cycle\Database\Exception\StatementException\ConstrainException;
use DateTimeImmutable;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Sector;
use FinGather\Model\Entity\SectorData;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\SectorDataRepository;
use FinGather\Utils\DateTimeUtils;

class SectorDataProvider
{
	public function __construct(
		private readonly SectorDataRepository $sectorDataRepository,
		private readonly CalculatedDataProvider $calculatedDataProvider,
	) {
	}

	public function getSectorData(Sector $sector, User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): SectorData
	{
		$dateTime = DateTimeUtils::setEndOfDateTime($dateTime);

		$sectorData = $this->sectorDataRepository->findSectorData($sector->getId(), $portfolio->getId(), $dateTime);
		if ($sectorData !== null) {
			return $sectorData;
		}

		$calculatedData = $this->calculatedDataProvider->getCalculatedDate($user, $portfolio, $dateTime, sector: $sector);

		$sectorData = new SectorData(
			sector: $sector,
			user: $user,
			portfolio: $portfolio,
			date: $dateTime,
			value: $calculatedData->value,
			transactionValue: $calculatedData->transactionValue,
			gain: $calculatedData->gain,
			gainPercentage: $calculatedData->gainPercentage,
			gainPercentagePerAnnum: $calculatedData->gainPercentagePerAnnum,
			realizedGain: $calculatedData->realizedGain,
			dividendYield: $calculatedData->dividendYield,
			dividendYieldPercentage: $calculatedData->dividendYieldPercentage,
			dividendYieldPercentagePerAnnum: $calculatedData->dividendYieldPercentagePerAnnum,
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
			$this->sectorDataRepository->persist($sectorData);
		} catch (ConstrainException) {
			$sectorData = $this->sectorDataRepository->findSectorData($sector->getId(), $sector->getId(), $dateTime);
			assert($sectorData instanceof SectorData);
		}

		return $sectorData;
	}

	public function deleteUserSectorData(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $date = null): void
	{
		$this->sectorDataRepository->deleteUserSectorData($user?->getId(), $portfolio?->getId(), $date);
	}
}
