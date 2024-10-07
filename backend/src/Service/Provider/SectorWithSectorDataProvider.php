<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Dto\GroupDataDto;
use FinGather\Dto\SectorWithSectorDataDto;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Utils\CalculatorUtils;
use Safe\DateTimeImmutable;

class SectorWithSectorDataProvider
{
	public function __construct(
		private readonly PortfolioDataProvider $portfolioDataProvider,
		private readonly SectorProvider $sectorProvider,
		private readonly SectorDataProvider $sectorDataProvider,
	) {
	}

	/** @return list<SectorWithSectorDataDto> */
	public function getSectorsWithSectorData(User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): array
	{
		$portfolioData = $this->portfolioDataProvider->getPortfolioData($user, $portfolio, $dateTime);

		$sectors = $this->sectorProvider->getSectorsFromAssets($user, $portfolio, $dateTime);

		$sectorsWithCountryData = [];

		foreach	($sectors as $sectorId => $sector) {
			$sectorData = $this->sectorDataProvider->getSectorData($sector, $user, $portfolio, $dateTime);
			if ($sectorData->value->isZero()) {
				continue;
			}

			$sectorsWithCountryData[] = new SectorWithSectorDataDto(
				id: $sectorId,
				userId: $user->getId(),
				name: $sector->getName(),
				percentage: CalculatorUtils::toPercentage($sectorData->value, $portfolioData->value),
				groupData: GroupDataDto::fromCalculatedDataDto($sectorData),
			);
		}

		return $sectorsWithCountryData;
	}
}
