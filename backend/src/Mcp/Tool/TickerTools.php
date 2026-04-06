<?php

declare(strict_types=1);

namespace FinGather\Mcp\Tool;

use FinGather\Mcp\Dto\McpTickerDto;
use FinGather\Mcp\Dto\McpTickerListDto;
use FinGather\Service\Provider\TickerProviderInterface;
use Mcp\Capability\Attribute\McpTool;

final readonly class TickerTools
{
	public function __construct(private TickerProviderInterface $tickerProvider)
	{
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
}
