<?php

declare(strict_types=1);

namespace FinGather\Mcp\Tool;

use FinGather\Dto\Enum\RangeEnum;
use FinGather\Mcp\Dto\McpDividendCalendarItemDto;
use FinGather\Mcp\Dto\McpDividendDataDto;
use FinGather\Mcp\Dto\McpDividendIntervalDto;
use FinGather\Mcp\McpUserContextInterface;
use FinGather\Service\Provider\DividendCalendarProviderInterface;
use FinGather\Service\Provider\DividendDataProviderInterface;
use FinGather\Service\Provider\PortfolioProviderInterface;
use Mcp\Capability\Attribute\McpTool;

final readonly class DividendTools
{
	public function __construct(
		private McpUserContextInterface $userContext,
		private PortfolioProviderInterface $portfolioProvider,
		private DividendDataProviderInterface $dividendDataProvider,
		private DividendCalendarProviderInterface $dividendCalendarProvider,
	) {
	}

	/**
	 * Get dividend income history and upcoming dividend calendar for a portfolio.
	 * History shows dividend income grouped by time intervals.
	 * Calendar shows upcoming expected dividend payments.
	 * All monetary values are in the portfolio's default currency.
	 *
	 * @param int $portfolioId Portfolio ID
	 * @param string $range Time range for history: SevenDays, OneMonth, ThreeMonths, SixMonths, YTD, OneYear, All (default All)
	 */
	#[McpTool(name: 'get_dividend_data', description: 'Get dividend income history and upcoming dividend calendar')]
	public function getDividendData(int $portfolioId, string $range = 'All'): McpDividendDataDto
	{
		$user = $this->userContext->getUser();

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			throw new \RuntimeException(sprintf('Portfolio %d not found.', $portfolioId));
		}

		$rangeEnum = RangeEnum::from($range);

		$history = [];
		foreach ($this->dividendDataProvider->getDividendData($user, $portfolio, $rangeEnum) as $interval) {
			$history[] = McpDividendIntervalDto::fromDividendDataInterval($interval);
		}

		$calendar = [];
		foreach ($this->dividendCalendarProvider->getDividendCalendar($user, $portfolio) as $item) {
			$calendar[] = McpDividendCalendarItemDto::fromDividendCalendarItem($item);
		}

		return new McpDividendDataDto(
			portfolioId: $portfolio->id,
			currency: $portfolio->currency->code,
			history: $history,
			calendar: $calendar,
		);
	}
}
