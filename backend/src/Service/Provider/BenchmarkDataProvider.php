<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\BenchmarkData;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\BenchmarkDataRepository;
use FinGather\Service\DataCalculator\BenchmarkDataCalculator;

class BenchmarkDataProvider
{
	public function __construct(
		private readonly BenchmarkDataRepository $benchmarkDataRepository,
		private readonly BenchmarkDataCalculator $benchmarkDataCalculator,
		private readonly ExchangeRateProvider $exchangeRateProvider,
		private readonly TickerDataProvider $tickerDataProvider,
	) {
	}

	/** @param list<Transaction> $transactions */
	public function getBenchmarkData(
		User $user,
		Portfolio $portfolio,
		Asset $benchmarkAsset,
		array $transactions,
		DateTimeImmutable $dateTime,
		DateTimeImmutable $benchmarkFromDateTime,
		Decimal $benchmarkFromDateUnits,
	): BenchmarkData {
		$dateTime = $dateTime->setTime(0, 0);
		$benchmarkFromDateTime = $benchmarkFromDateTime->setTime(0, 0);

		$benchmarkData = $this->benchmarkDataRepository->findBenchmarkData(
			$portfolio->getId(),
			$benchmarkAsset->getId(),
			$dateTime,
			$benchmarkFromDateTime,
		);
		if ($benchmarkData !== null) {
			return $benchmarkData;
		}

		$benchmarkDataDto = $this->benchmarkDataCalculator->calculate(
			$portfolio,
			$transactions,
			$benchmarkAsset,
			$dateTime,
			$benchmarkFromDateTime,
			$benchmarkFromDateUnits,
		);

		$benchmarkData = new BenchmarkData(
			user: $user,
			portfolio: $portfolio,
			asset: $benchmarkAsset,
			date: $dateTime,
			fromDate: $benchmarkFromDateTime,
			value: $benchmarkDataDto->value,
			units: $benchmarkDataDto->units,
		);

		$this->benchmarkDataRepository->persist($benchmarkData);

		return $benchmarkData;
	}

	public function getBenchmarkDataFromDate(
		User $user,
		Portfolio $portfolio,
		Asset $benchmarkAsset,
		DateTimeImmutable $benchmarkFromDateTime,
		Decimal $portfolioDataValue,
	): BenchmarkData {
		$benchmarkFromDateTime = $benchmarkFromDateTime->setTime(0, 0);

		$benchmarkData = $this->benchmarkDataRepository->findBenchmarkData(
			$portfolio->getId(),
			$benchmarkAsset->getId(),
			$benchmarkFromDateTime,
			$benchmarkFromDateTime,
		);
		if ($benchmarkData !== null) {
			return $benchmarkData;
		}

		$benchmarkTickerCurrency = $benchmarkAsset->getTicker()->getCurrency();

		$benchmarkAssetTickerData = $this->tickerDataProvider->getLastTickerData($benchmarkAsset->getTicker(), $benchmarkFromDateTime);
		if ($benchmarkAssetTickerData !== null) {
			$benchmarkExchangeRateDefaultCurrency = $this->exchangeRateProvider->getExchangeRate(
				$benchmarkFromDateTime,
				$benchmarkTickerCurrency,
				$portfolio->getCurrency(),
			);

			$benchmarkUnitPriceDefaultCurrency = $benchmarkAssetTickerData->getClose()->mul($benchmarkExchangeRateDefaultCurrency);

			$benchmarkUnits = $portfolioDataValue->div($benchmarkUnitPriceDefaultCurrency);
		} else {
			$benchmarkUnits = new Decimal(0);
		}

		$benchmarkData = new BenchmarkData(
			user: $user,
			portfolio: $portfolio,
			asset: $benchmarkAsset,
			date: $benchmarkFromDateTime,
			fromDate: $benchmarkFromDateTime,
			value: $portfolioDataValue,
			units: $benchmarkUnits,
		);

		$this->benchmarkDataRepository->persist($benchmarkData);

		return $benchmarkData;
	}

	public function deleteBenchmarkData(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $date = null): void
	{
		$this->benchmarkDataRepository->deleteBenchmarkData($user?->getId(), $portfolio?->getId(), $date);
	}
}
