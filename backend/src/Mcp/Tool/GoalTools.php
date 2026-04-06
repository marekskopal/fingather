<?php

declare(strict_types=1);

namespace FinGather\Mcp\Tool;

use DateTimeImmutable;
use FinGather\Mcp\Dto\McpGoalDto;
use FinGather\Mcp\Dto\McpGoalListDto;
use FinGather\Mcp\McpUserContextInterface;
use FinGather\Service\Provider\GoalProviderInterface;
use FinGather\Service\Provider\PortfolioDataProviderInterface;
use FinGather\Service\Provider\PortfolioProviderInterface;
use Mcp\Capability\Attribute\McpTool;
use RuntimeException;

final readonly class GoalTools
{
	public function __construct(
		private McpUserContextInterface $userContext,
		private PortfolioProviderInterface $portfolioProvider,
		private PortfolioDataProviderInterface $portfolioDataProvider,
		private GoalProviderInterface $goalProvider,
	) {
	}

	/**
	 * List financial goals for a portfolio with current progress.
	 * Shows how close the portfolio value is to each goal's target.
	 */
	#[McpTool(name: 'list_goals', description: 'List financial goals with current progress towards each target')]
	public function listGoals(int $portfolioId): McpGoalListDto
	{
		$user = $this->userContext->getUser();

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			throw new RuntimeException(sprintf('Portfolio %d not found.', $portfolioId));
		}

		$currentValue = $this->portfolioDataProvider
			->getPortfolioData($user, $portfolio, new DateTimeImmutable())
			->value;

		$goals = [];
		foreach ($this->goalProvider->getGoals($user, $portfolio) as $goal) {
			$goals[] = McpGoalDto::fromEntity($goal, $currentValue);
		}

		return new McpGoalListDto($goals);
	}
}
