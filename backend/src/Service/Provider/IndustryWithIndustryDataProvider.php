<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Dto\IndustryDataDto;
use FinGather\Dto\IndustryWithIndustryDataDto;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Utils\CalculatorUtils;
use Safe\DateTimeImmutable;

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

			$industriesWithIndustryData[] = new IndustryWithIndustryDataDto(
				id: $industryId,
				userId: $user->getId(),
				name: $industry->getName(),
				percentage: CalculatorUtils::toPercentage($industryData->getValue(), $portfolioData->getValue()),
				industryData: IndustryDataDto::fromEntity($industryData),
			);
		}

		return $industriesWithIndustryData;
	}
}
