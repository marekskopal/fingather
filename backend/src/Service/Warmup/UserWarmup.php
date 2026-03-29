<?php

declare(strict_types=1);

namespace FinGather\Service\Warmup;

use DateInterval;
use DateTimeImmutable;
use FinGather\Helper\DatePeriod;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Service\Provider\DataProviderInterface;
use FinGather\Service\Provider\PortfolioDataProviderInterface;
use FinGather\Service\Provider\PortfolioProviderInterface;
use FinGather\Service\Provider\TransactionProviderInterface;

final readonly class UserWarmup
{
	public function __construct(
		private PortfolioProviderInterface $portfolioProvider,
		private PortfolioDataProviderInterface $portfolioDataProvider,
		private TransactionProviderInterface $transactionProvider,
		private DataProviderInterface $dataProvider,
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

		$this->dataProvider->deleteData(user: $user, portfolio: $portfolio, firstDate: $dateTime);

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
