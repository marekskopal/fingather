<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Dto\GroupDataDto;
use FinGather\Dto\IndustryWithIndustryDataDto;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Utils\CalculatorUtils;

class IndustryWithIndustryDataProvider
{
	public function __construct(
		private readonly PortfolioDataProvider $portfolioDataProvider,
		private readonly IndustryProvider $industryProvider,
		private readonly IndustryDataProvider $industryDataProvider,
	) {
	}

	/** @return list<IndustryWithIndustryDataDto> */
	public function getIndustriesWithIndustryData(User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): array
	{
		$portfolioData = $this->portfolioDataProvider->getPortfolioData($user, $portfolio, $dateTime);

		$industries = $this->industryProvider->getIndustriesFromAssets($user, $portfolio, $dateTime);

		$industriesWithIndustryData = [];

		foreach	($industries as $industryId => $industry) {
			$industryData = $this->industryDataProvider->getIndustryData($industry, $user, $portfolio, $dateTime);
			if ($industryData->value->isZero()) {
				continue;
			}

			$industriesWithIndustryData[] = new IndustryWithIndustryDataDto(
				id: $industryId,
				userId: $user->getId(),
				name: $industry->getName(),
				percentage: CalculatorUtils::toPercentage($industryData->value, $portfolioData->value),
				groupData: GroupDataDto::fromCalculatedDataDto($industryData),
			);
		}

		return $industriesWithIndustryData;
	}
}
