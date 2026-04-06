<?php

declare(strict_types=1);

namespace FinGather\Mcp\Tool;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Mcp\Dto\McpRebalancingDto;
use FinGather\Mcp\Dto\McpStrategyDto;
use FinGather\Mcp\Dto\McpStrategyListDto;
use FinGather\Mcp\McpUserContextInterface;
use FinGather\Service\Provider\PortfolioProviderInterface;
use FinGather\Service\Provider\StrategyProviderInterface;
use FinGather\Service\Provider\StrategyRebalancingProviderInterface;
use Mcp\Capability\Attribute\McpTool;
use RuntimeException;

final readonly class StrategyTools
{
	public function __construct(
		private McpUserContextInterface $userContext,
		private PortfolioProviderInterface $portfolioProvider,
		private StrategyProviderInterface $strategyProvider,
		private StrategyRebalancingProviderInterface $strategyRebalancingProvider,
	) {
	}

	/**
	 * List all allocation strategies for a portfolio.
	 * Each strategy contains target percentage allocations per asset or group.
	 */
	#[McpTool(name: 'list_strategies', description: 'List portfolio allocation strategies with target percentages')]
	public function listStrategies(int $portfolioId): McpStrategyListDto
	{
		$portfolio = $this->portfolioProvider->getPortfolio($this->userContext->getUser(), $portfolioId);
		if ($portfolio === null) {
			throw new RuntimeException(sprintf('Portfolio %d not found.', $portfolioId));
		}

		$strategies = [];
		foreach ($this->strategyProvider->getStrategies($this->userContext->getUser(), $portfolio) as $strategy) {
			$strategies[] = McpStrategyDto::fromEntity($strategy);
		}

		return new McpStrategyListDto($strategies);
	}

	/**
	 * Get rebalancing suggestions for a strategy.
	 * Shows how much to buy or sell for each position to reach target allocations.
	 * Pass cashToInvest to include new capital in the calculations.
	 *
	 * @param int $portfolioId Portfolio ID
	 * @param int $strategyId Strategy ID (use list_strategies to find IDs)
	 * @param string $cashToInvest Additional cash to invest in portfolio currency (default "0")
	 * @param bool $allowSelling Whether the suggestions may include sell orders (default false)
	 */
	#[McpTool(
		name: 'get_rebalancing',
		description: 'Get suggested buy/sell orders to rebalance a portfolio to its strategy target allocations',
	)]
	public function getRebalancing(
		int $portfolioId,
		int $strategyId,
		string $cashToInvest = '0',
		bool $allowSelling = false,
	): McpRebalancingDto
	{
		$portfolio = $this->portfolioProvider->getPortfolio($this->userContext->getUser(), $portfolioId);
		if ($portfolio === null) {
			throw new RuntimeException(sprintf('Portfolio %d not found.', $portfolioId));
		}

		$strategy = $this->strategyProvider->getStrategy($this->userContext->getUser(), $strategyId);
		if ($strategy === null) {
			throw new RuntimeException(sprintf('Strategy %d not found.', $strategyId));
		}

		$rebalancing = $this->strategyRebalancingProvider->getStrategyRebalancing(
			user: $this->userContext->getUser(),
			portfolio: $portfolio,
			strategy: $strategy,
			dateTime: new DateTimeImmutable(),
			cashToInvest: new Decimal($cashToInvest),
			cashCurrencyId: null,
			allowSelling: $allowSelling,
		);

		return McpRebalancingDto::fromDto($rebalancing, $portfolio, $allowSelling);
	}
}
