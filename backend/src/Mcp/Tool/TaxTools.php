<?php

declare(strict_types=1);

namespace FinGather\Mcp\Tool;

use FinGather\Mcp\Dto\McpTaxReportDto;
use FinGather\Mcp\McpUserContextInterface;
use FinGather\Service\DataCalculator\TaxReportCalculator;
use FinGather\Service\Provider\PortfolioProviderInterface;
use Mcp\Capability\Attribute\McpTool;
use RuntimeException;

final readonly class TaxTools
{
	public function __construct(
		private McpUserContextInterface $userContext,
		private PortfolioProviderInterface $portfolioProvider,
		private TaxReportCalculator $taxReportCalculator,
	) {
	}

	/**
	 * Get a tax report for a given year and portfolio.
	 * Includes FIFO-calculated realized gains, dividend income, unrealized positions, and total fees/taxes paid.
	 * All monetary values are in the portfolio's default currency.
	 */
	#[McpTool(name: 'get_tax_report', description: 'Get realized gains, dividends, fees, and taxes for a given year')]
	public function getTaxReport(int $portfolioId, int $year): McpTaxReportDto
	{
		$user = $this->userContext->getUser();

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			throw new RuntimeException(sprintf('Portfolio %d not found.', $portfolioId));
		}

		$report = $this->taxReportCalculator->calculate($user, $portfolio, $year);

		return McpTaxReportDto::fromDto($report, $portfolio);
	}
}
