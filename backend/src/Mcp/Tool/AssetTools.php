<?php

declare(strict_types=1);

namespace FinGather\Mcp\Tool;

use DateTimeImmutable;
use FinGather\Mcp\Dto\McpAssetDetailDto;
use FinGather\Mcp\Dto\McpAssetDto;
use FinGather\Mcp\Dto\McpAssetListDto;
use FinGather\Mcp\McpUserContextInterface;
use FinGather\Service\Provider\AssetDataProviderInterface;
use FinGather\Service\Provider\AssetProviderInterface;
use FinGather\Service\Provider\PortfolioProviderInterface;
use Mcp\Capability\Attribute\McpTool;

final readonly class AssetTools
{
	public function __construct(
		private McpUserContextInterface $userContext,
		private PortfolioProviderInterface $portfolioProvider,
		private AssetProviderInterface $assetProvider,
		private AssetDataProviderInterface $assetDataProvider,
	) {
	}

	/**
	 * List all holdings (assets) in a portfolio with their current value and performance.
	 * Only open positions (units > 0) are returned.
	 * All monetary values are in the portfolio's default currency unless noted.
	 */
	#[McpTool(name: 'list_assets', description: 'List all open holdings in a portfolio with performance data')]
	public function listAssets(int $portfolioId): McpAssetListDto
	{
		$user = $this->userContext->getUser();

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			throw new \RuntimeException(sprintf('Portfolio %d not found.', $portfolioId));
		}

		$now = new DateTimeImmutable();
		$assets = [];

		foreach ($this->assetProvider->getAssets(user: $user, portfolio: $portfolio, dateTime: $now) as $asset) {
			$data = $this->assetDataProvider->getAssetData($user, $portfolio, $asset, $now);
			if ($data === null || $data->isClosed()) {
				continue;
			}

			$assets[] = McpAssetDto::fromAssetData($asset, $data);
		}

		return new McpAssetListDto($assets);
	}

	/**
	 * Get full performance detail for a single asset.
	 * Includes average price, realized/unrealized gain, dividend yield, FX impact, and annualised returns.
	 */
	#[McpTool(name: 'get_asset_detail', description: 'Get detailed performance data for a single holding')]
	public function getAssetDetail(int $assetId): McpAssetDetailDto
	{
		$user = $this->userContext->getUser();

		$asset = $this->assetProvider->getAsset($user, $assetId);
		if ($asset === null) {
			throw new \RuntimeException(sprintf('Asset %d not found.', $assetId));
		}

		$portfolio = $asset->portfolio;
		$now = new DateTimeImmutable();

		$data = $this->assetDataProvider->getAssetData($user, $portfolio, $asset, $now);
		if ($data === null) {
			throw new \RuntimeException(sprintf('No data available for asset %d.', $assetId));
		}

		return McpAssetDetailDto::fromAssetData($asset, $data);
	}
}
