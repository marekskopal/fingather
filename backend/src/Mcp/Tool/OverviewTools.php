<?php

declare(strict_types=1);

namespace FinGather\Mcp\Tool;

use FinGather\Mcp\Dto\McpYearDataDto;
use FinGather\Mcp\Dto\McpYearOverviewDto;
use FinGather\Mcp\McpUserContextInterface;
use FinGather\Service\DataCalculator\OverviewDataCalculator;
use FinGather\Service\Provider\PortfolioProviderInterface;
use Mcp\Capability\Attribute\McpTool;

final readonly class OverviewTools
{
	public function __construct(
		private McpUserContextInterface $userContext,
		private PortfolioProviderInterface $portfolioProvider,
		private OverviewDataCalculator $overviewDataCalculator,
	) {
	}

	/**
	 * Get year-by-year performance overview for a portfolio.
	 * Returns cumulative metrics and interannual changes (suffixed with "Change") for each year.
	 * All monetary values are in the portfolio's default currency.
	 */
	#[McpTool(name: 'get_year_overview', description: 'Get year-by-year performance comparison with interannual changes')]
	public function getYearOverview(int $portfolioId): McpYearOverviewDto
	{
		$user = $this->userContext->getUser();

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			throw new \RuntimeException(sprintf('Portfolio %d not found.', $portfolioId));
		}

		$yearData = $this->overviewDataCalculator->yearCalculate($user, $portfolio);

		$years = [];
		foreach ($yearData as $data) {
			$years[] = McpYearDataDto::fromYearCalculatedData($data);
		}

		return new McpYearOverviewDto(portfolioId: $portfolio->id, currency: $portfolio->currency->code, years: $years);
	}
}
