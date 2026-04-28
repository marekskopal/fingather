<?php

declare(strict_types=1);

namespace FinGather\Mcp\Tool;

use FinGather\Mcp\Dto\McpTickerDto;
use FinGather\Mcp\Dto\McpTickerFundamentalsDto;
use FinGather\Mcp\Dto\McpTickerListDto;
use FinGather\Service\Provider\TickerFundamentalProviderInterface;
use FinGather\Service\Provider\TickerProviderInterface;
use Mcp\Capability\Attribute\McpTool;

final readonly class TickerTools
{
	public function __construct(
		private TickerProviderInterface $tickerProvider,
		private TickerFundamentalProviderInterface $tickerFundamentalProvider,
	) {
	}

	/**
	 * Search for tickers (stocks, ETFs, crypto, etc.) by symbol or name.
	 * Use this to find the ticker information before adding transactions or looking up asset IDs.
	 *
	 * @param string $query Ticker symbol or company name to search for (e.g. "AAPL" or "Apple")
	 * @param int $limit Maximum number of results (default 20, max 50)
	 */
	#[McpTool(name: 'search_tickers', description: 'Search for tickers by symbol or company name')]
	public function searchTickers(string $query, int $limit = 20): McpTickerListDto
	{
		$limit = min($limit, 50);

		$tickers = [];
		foreach ($this->tickerProvider->getTickers(search: $query, limit: $limit) as $ticker) {
			$tickers[] = McpTickerDto::fromEntity($ticker);
		}

		return new McpTickerListDto($tickers);
	}

	/**
	 * Get fundamental financial data for a ticker.
	 * Includes valuation (PE, PEG, P/B, EV/EBITDA), profitability (margins, ROE, ROA),
	 * growth (revenue/earnings growth), balance sheet (cash, debt), cash flow, dividend info,
	 * trading data (52-week range, beta, moving averages), and share statistics.
	 *
	 * @param int $tickerId Ticker ID (from search_tickers or list_assets)
	 */
	#[McpTool(
		name: 'get_ticker_fundamentals',
		description: 'Get fundamental financial data for a ticker (PE ratio, market cap, margins, dividends, etc.)',
	)]
	public function getTickerFundamentals(int $tickerId): McpTickerFundamentalsDto
	{
		$ticker = $this->tickerProvider->getTicker($tickerId);
		if ($ticker === null) {
			throw new \RuntimeException(sprintf('Ticker %d not found.', $tickerId));
		}

		$fundamental = $this->tickerFundamentalProvider->getTickerFundamental($ticker);
		if ($fundamental === null) {
			throw new \RuntimeException(sprintf('No fundamental data available for ticker %d.', $tickerId));
		}

		return McpTickerFundamentalsDto::fromEntity($ticker, $fundamental);
	}
}
