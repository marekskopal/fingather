<?php

declare(strict_types=1);

namespace FinGather\Mcp\Tool;

use DateTimeImmutable;
use FinGather\Dto\Enum\RangeEnum;
use FinGather\Mcp\Dto\McpAssetDetailDto;
use FinGather\Mcp\Dto\McpAssetDto;
use FinGather\Mcp\Dto\McpAssetHistoryDto;
use FinGather\Mcp\Dto\McpAssetHistoryPointDto;
use FinGather\Mcp\Dto\McpAssetListDto;
use FinGather\Mcp\McpUserContextInterface;
use FinGather\Service\Provider\AssetDataProviderInterface;
use FinGather\Service\Provider\AssetProviderInterface;
use FinGather\Service\Provider\PortfolioProviderInterface;
use FinGather\Service\Provider\TransactionProviderInterface;
use FinGather\Utils\DateTimeUtils;
use Mcp\Capability\Attribute\McpTool;

final readonly class AssetTools
{
	public function __construct(
		private McpUserContextInterface $userContext,
		private PortfolioProviderInterface $portfolioProvider,
		private AssetProviderInterface $assetProvider,
		private AssetDataProviderInterface $assetDataProvider,
		private TransactionProviderInterface $transactionProvider,
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

	/**
	 * Get historical performance data for a single asset over a time range.
	 * Returns data points with price, value, gain, and return over time.
	 * All monetary values are in the portfolio's default currency.
	 *
	 * @param int $assetId Asset ID (from list_assets)
	 * @param string $range Time range: SevenDays, OneMonth, ThreeMonths, SixMonths, YTD, OneYear, All
	 */
	#[McpTool(name: 'get_asset_history', description: 'Get historical performance data for a single asset over a time range')]
	public function getAssetHistory(int $assetId, string $range): McpAssetHistoryDto
	{
		$user = $this->userContext->getUser();

		$asset = $this->assetProvider->getAsset($user, $assetId);
		if ($asset === null) {
			throw new \RuntimeException(sprintf('Asset %d not found.', $assetId));
		}

		$portfolio = $asset->portfolio;
		$rangeEnum = RangeEnum::from($range);

		$firstTransaction = $this->transactionProvider->getFirstTransaction($user, $portfolio, $asset);
		if ($firstTransaction === null) {
			return new McpAssetHistoryDto(
				assetId: $asset->id,
				ticker: $asset->ticker->ticker,
				name: $asset->ticker->name,
				currency: $portfolio->currency->code,
				range: $range,
				dataPoints: [],
			);
		}

		$datePeriod = DateTimeUtils::getDatePeriod(
			range: $rangeEnum,
			firstDate: $firstTransaction->actionCreated,
			shiftStartDate: $rangeEnum === RangeEnum::All,
		);

		$dataPoints = [];
		foreach ($datePeriod as $dateTime) {
			/** @var DateTimeImmutable $dateTime */
			$assetData = $this->assetDataProvider->getAssetData(user: $user, portfolio: $portfolio, asset: $asset, dateTime: $dateTime);
			if ($assetData === null) {
				continue;
			}

			$dataPoints[] = McpAssetHistoryPointDto::fromAssetData($assetData);
		}

		return new McpAssetHistoryDto(
			assetId: $asset->id,
			ticker: $asset->ticker->ticker,
			name: $asset->ticker->name,
			currency: $portfolio->currency->code,
			range: $range,
			dataPoints: $dataPoints,
		);
	}
}
