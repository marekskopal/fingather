<?php

declare(strict_types=1);

namespace FinGather\Mcp\Tool;

use DateTimeImmutable;
use FinGather\Mcp\Dto\McpPortfolioDto;
use FinGather\Mcp\Dto\McpPortfolioListDto;
use FinGather\Mcp\Dto\McpPortfolioSummaryDto;
use FinGather\Mcp\McpUserContextInterface;
use FinGather\Service\Provider\PortfolioDataProviderInterface;
use FinGather\Service\Provider\PortfolioProviderInterface;
use Mcp\Capability\Attribute\McpTool;

final readonly class PortfolioTools
{
	public function __construct(
		private McpUserContextInterface $userContext,
		private PortfolioProviderInterface $portfolioProvider,
		private PortfolioDataProviderInterface $portfolioDataProvider,
	) {
	}

	/** List all portfolios belonging to the authenticated user. */
	#[McpTool(name: 'list_portfolios', description: 'List all portfolios for the user')]
	public function listPortfolios(): McpPortfolioListDto
	{
		$portfolios = [];
		foreach ($this->portfolioProvider->getPortfolios($this->userContext->getUser()) as $portfolio) {
			$portfolios[] = McpPortfolioDto::fromEntity($portfolio);
		}

		return new McpPortfolioListDto($portfolios);
	}

	/**
	 * Get summary data for a portfolio: current value, cost basis, total return and unrealized gain.
	 * All monetary values are in the portfolio's default currency.
	 */
	#[McpTool(name: 'get_portfolio_summary', description: 'Get current value, returns, and performance metrics for a portfolio')]
	public function getPortfolioSummary(int $portfolioId): McpPortfolioSummaryDto
	{
		$user = $this->userContext->getUser();

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			throw new \RuntimeException(sprintf('Portfolio %d not found.', $portfolioId));
		}

		$data = $this->portfolioDataProvider->getPortfolioData($user, $portfolio, new DateTimeImmutable());

		return McpPortfolioSummaryDto::fromPortfolioData($portfolio, $data);
	}
}
