<?php

declare(strict_types=1);

namespace FinGather\Mcp\Tool;

use DateTimeImmutable;
use FinGather\Dto\Enum\RangeEnum;
use FinGather\Mcp\Dto\McpPortfolioHistoryDto;
use FinGather\Mcp\Dto\McpPortfolioHistoryPointDto;
use FinGather\Mcp\McpUserContextInterface;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Service\Provider\CurrentTransactionProviderInterface;
use FinGather\Service\Provider\PortfolioDataProviderInterface;
use FinGather\Service\Provider\PortfolioProviderInterface;
use FinGather\Utils\DateTimeUtils;
use Mcp\Capability\Attribute\McpTool;

final readonly class PortfolioHistoryTools
{
	public function __construct(
		private McpUserContextInterface $userContext,
		private PortfolioProviderInterface $portfolioProvider,
		private PortfolioDataProviderInterface $portfolioDataProvider,
		private CurrentTransactionProviderInterface $currentTransactionProvider,
	) {
	}

	/**
	 * Get portfolio performance history over a time range.
	 * Returns data points with value, gain, return, dividends, and FX impact over time.
	 * All monetary values are in the portfolio's default currency.
	 *
	 * @param int $portfolioId Portfolio ID
	 * @param string $range Time range: SevenDays, OneMonth, ThreeMonths, SixMonths, YTD, OneYear, All
	 */
	#[McpTool(name: 'get_portfolio_history', description: 'Get portfolio performance history over a time range (value, gain, return over time)')]
	public function getPortfolioHistory(int $portfolioId, string $range): McpPortfolioHistoryDto
	{
		$user = $this->userContext->getUser();

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			throw new \RuntimeException(sprintf('Portfolio %d not found.', $portfolioId));
		}

		$rangeEnum = RangeEnum::from($range);

		$transactions = $this->currentTransactionProvider->getTransactions(
			user: $user,
			portfolio: $portfolio,
			actionTypes: [TransactionActionTypeEnum::Buy, TransactionActionTypeEnum::Sell],
		);

		if (count($transactions) === 0) {
			return new McpPortfolioHistoryDto(
				portfolioId: $portfolio->id,
				currency: $portfolio->currency->code,
				range: $range,
				dataPoints: [],
			);
		}

		$firstTransaction = array_first($transactions);

		$datePeriod = DateTimeUtils::getDatePeriod(
			range: $rangeEnum,
			firstDate: $firstTransaction->actionCreated,
			shiftStartDate: $rangeEnum === RangeEnum::All,
		);

		$dataPoints = [];
		foreach ($datePeriod as $dateTime) {
			/** @var DateTimeImmutable $dateTime */
			$portfolioData = $this->portfolioDataProvider->getPortfolioData($user, $portfolio, $dateTime);
			$dataPoints[] = McpPortfolioHistoryPointDto::fromCalculatedData($portfolioData);
		}

		return new McpPortfolioHistoryDto(
			portfolioId: $portfolio->id,
			currency: $portfolio->currency->code,
			range: $range,
			dataPoints: $dataPoints,
		);
	}
}
