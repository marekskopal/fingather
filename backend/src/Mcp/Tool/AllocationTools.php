<?php

declare(strict_types=1);

namespace FinGather\Mcp\Tool;

use DateTimeImmutable;
use FinGather\Dto\AbstractGroupWithGroupDataDto;
use FinGather\Mcp\Dto\McpAllocationDto;
use FinGather\Mcp\Dto\McpAllocationItemDto;
use FinGather\Mcp\McpUserContextInterface;
use FinGather\Service\Provider\CountryWithCountryDataProviderInterface;
use FinGather\Service\Provider\GroupWithGroupDataProviderInterface;
use FinGather\Service\Provider\IndustryWithIndustryDataProviderInterface;
use FinGather\Service\Provider\PortfolioProviderInterface;
use FinGather\Service\Provider\SectorWithSectorDataProviderInterface;
use Mcp\Capability\Attribute\McpTool;

final readonly class AllocationTools
{
	public function __construct(
		private McpUserContextInterface $userContext,
		private PortfolioProviderInterface $portfolioProvider,
		private SectorWithSectorDataProviderInterface $sectorProvider,
		private IndustryWithIndustryDataProviderInterface $industryProvider,
		private CountryWithCountryDataProviderInterface $countryProvider,
		private GroupWithGroupDataProviderInterface $groupProvider,
	) {
	}

	/**
	 * Get portfolio allocation breakdown by sector, industry, country, or custom group.
	 * Returns each category with its percentage weight, value, and performance metrics.
	 * All monetary values are in the portfolio's default currency.
	 *
	 * @param int $portfolioId Portfolio ID
	 * @param string $type Allocation type: sector, industry, country, group
	 */
	#[McpTool(name: 'get_portfolio_allocation', description: 'Get portfolio allocation breakdown by sector, industry, country, or custom group')]
	public function getPortfolioAllocation(int $portfolioId, string $type): McpAllocationDto
	{
		$user = $this->userContext->getUser();

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			throw new \RuntimeException(sprintf('Portfolio %d not found.', $portfolioId));
		}

		$dateTime = new DateTimeImmutable();

		$groupData = match ($type) {
			'sector' => $this->sectorProvider->getSectorsWithSectorData($user, $portfolio, $dateTime),
			'industry' => $this->industryProvider->getIndustriesWithIndustryData($user, $portfolio, $dateTime),
			'country' => $this->countryProvider->getCountriesWithCountryData($user, $portfolio, $dateTime),
			'group' => $this->groupProvider->getGroupsWithGroupData($user, $portfolio, $dateTime),
			default => throw new \RuntimeException(sprintf('Invalid allocation type "%s". Use: sector, industry, country, group.', $type)),
		};

		$items = [];
		foreach ($groupData as $group) {
			$items[] = McpAllocationItemDto::fromGroupWithGroupData($group);
		}

		return new McpAllocationDto(
			portfolioId: $portfolio->id,
			currency: $portfolio->currency->code,
			allocationType: $type,
			items: $items,
		);
	}
}
