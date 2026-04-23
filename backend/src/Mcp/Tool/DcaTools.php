<?php

declare(strict_types=1);

namespace FinGather\Mcp\Tool;

use FinGather\Mcp\Dto\McpDcaPlanDto;
use FinGather\Mcp\Dto\McpDcaProjectionPointDto;
use FinGather\Mcp\Dto\McpDcaProjectionsDto;
use FinGather\Mcp\McpUserContextInterface;
use FinGather\Service\Provider\DcaPlanProviderInterface;
use FinGather\Service\Provider\PortfolioProviderInterface;
use Mcp\Capability\Attribute\McpTool;

final readonly class DcaTools
{
	public function __construct(
		private McpUserContextInterface $userContext,
		private PortfolioProviderInterface $portfolioProvider,
		private DcaPlanProviderInterface $dcaPlanProvider,
	) {
	}

	/**
	 * List DCA (Dollar Cost Averaging) plans with their projected future values.
	 * Each plan includes the target, contribution amount, interval, historical return rate, and projected growth.
	 * All monetary values are in the plan's configured currency.
	 *
	 * @param int $portfolioId Portfolio ID
	 * @param int $horizonYears Projection horizon in years (default 10, max 30)
	 */
	#[McpTool(name: 'get_dca_projections', description: 'List DCA plans and their projected future values')]
	public function getDcaProjections(int $portfolioId, int $horizonYears = 10): McpDcaProjectionsDto
	{
		$user = $this->userContext->getUser();

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			throw new \RuntimeException(sprintf('Portfolio %d not found.', $portfolioId));
		}

		$horizonYears = min($horizonYears, 30);

		$plans = [];
		foreach ($this->dcaPlanProvider->getDcaPlans($user, $portfolio) as $plan) {
			$returnRate = $this->dcaPlanProvider->getReturnRate($plan);
			$projectionDto = $this->dcaPlanProvider->getProjection($plan, $horizonYears);

			$projectionPoints = [];
			foreach ($projectionDto->dataPoints as $point) {
				$projectionPoints[] = McpDcaProjectionPointDto::fromProjectionPoint($point);
			}

			$plans[] = McpDcaPlanDto::fromDcaPlan($plan, $returnRate, $projectionPoints);
		}

		return new McpDcaProjectionsDto(
			portfolioId: $portfolio->id,
			currency: $portfolio->currency->code,
			plans: $plans,
		);
	}
}
