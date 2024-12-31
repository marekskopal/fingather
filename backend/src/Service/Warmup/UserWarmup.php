<?php

declare(strict_types=1);

namespace FinGather\Service\Warmup;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Service\Provider\DataProvider;
use FinGather\Service\Provider\PortfolioDataProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Provider\TransactionProvider;

final class UserWarmup
{
	public function __construct(
		private readonly PortfolioProvider $portfolioProvider,
		private readonly PortfolioDataProvider $portfolioDataProvider,
		private readonly TransactionProvider $transactionProvider,
		private readonly DataProvider $dataProvider,
	) {
	}

	public function warmup(User $user): void
	{
		$portfolios = $this->portfolioProvider->getPortfolios(user: $user);
		foreach ($portfolios as $portfolio) {
			$this->warmupPortfolio($user, $portfolio);
		}
	}

	public function warmupPortfolio(User $user, Portfolio $portfolio): void
	{
		$dateTime = new DateTimeImmutable('today');

		$this->dataProvider->deleteData(user: $user, portfolio: $portfolio, date: $dateTime);

		$firstTransaction = $this->transactionProvider->getFirstTransaction(user: $user, portfolio: $portfolio);
		if ($firstTransaction === null) {
			return;
		}

		$datePeriod = new DatePeriod(
			start: $firstTransaction->actionCreated,
			interval: new DateInterval('P1D'),
			end: $dateTime,
		);
		foreach ($datePeriod as $date) {
			$this->portfolioDataProvider->getPortfolioData($user, $portfolio, $date);
		}
	}
}
