<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\BenchmarkDataDto;

interface BenchmarkDataProviderInterface
{
	/** @param list<Transaction> $transactions */
	public function getBenchmarkData(
		User $user,
		Portfolio $portfolio,
		Ticker $benchmarkTicker,
		array $transactions,
		DateTimeImmutable $dateTime,
		DateTimeImmutable $benchmarkFromDateTime,
		Decimal $benchmarkFromDateUnits,
	): BenchmarkDataDto;

	public function getBenchmarkDataFromDate(
		User $user,
		Portfolio $portfolio,
		Ticker $benchmarkTicker,
		DateTimeImmutable $benchmarkFromDateTime,
		Decimal $portfolioDataValue,
	): BenchmarkDataDto;

	public function deleteBenchmarkData(?User $user = null, ?Portfolio $portfolio = null): void;
}
