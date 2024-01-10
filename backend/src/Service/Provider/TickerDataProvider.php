<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use Cycle\Database\Exception\StatementException\ConstrainException;
use DateInterval;
use FinGather\Model\Entity\Enum\MarketTypeEnum;
use FinGather\Model\Entity\Split;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\TickerData;
use FinGather\Model\Repository\SplitRepository;
use FinGather\Model\Repository\TickerDataRepository;
use FinGather\Service\AlphaVantage\AlphaVantageApiClient;
use Safe\DateTime;
use Safe\DateTimeImmutable;

class TickerDataProvider
{
	public function __construct(
		private readonly TickerDataRepository $tickerDataRepository,
		private readonly SplitRepository $splitRepository,
		private readonly AlphaVantageApiClient $alphaVantageApiClient,
	) {
	}

	/** @return array<TickerData> */
	public function getTickerDatas(Ticker $ticker, DateTimeImmutable $fromDate, DateTimeImmutable $toDate): array
	{
		return $this->tickerDataRepository->findTickerDatas($ticker->getId(), $fromDate, $toDate);
	}

	public function getLastTickerData(Ticker $ticker, DateTimeImmutable $beforeDate): ?TickerData
	{
		$dayOfWeek = (int) $beforeDate->format('w');

		if ($dayOfWeek === 0) {
			$beforeDate = $beforeDate->sub(DateInterval::createFromDateString('2 days'));
		} elseif ($dayOfWeek === 6) {
			$beforeDate = $beforeDate->sub(DateInterval::createFromDateString('1 day'));
		}

		$lastTickerData = $this->tickerDataRepository->findLastTickerData($ticker->getId(), $beforeDate);
		if ($lastTickerData !== null) {
			return $lastTickerData;
		}

		$this->updateTickerData($ticker, true);

		return $this->tickerDataRepository->findLastTickerData($ticker->getId(), $beforeDate);
	}

	public function updateTickerData(Ticker $ticker, bool $fullHistory = false): void
	{
		$actualDate = new DateTime('today');

		$dayOfWeek = (int) $actualDate->format('w');

		if ($dayOfWeek === 0) {
			$actualDate->sub(DateInterval::createFromDateString('2 days'));
		} elseif ($dayOfWeek === 6) {
			$actualDate->sub(DateInterval::createFromDateString('1 day'));
		}

		$firstDate = (new DateTime('today'))->sub(DateInterval::createFromDateString('3 years'));

		$lastTickerData = $this->tickerDataRepository->findLastTickerData($ticker->getId());
		if ($lastTickerData !== null && ($actualDate->getTimestamp() - $lastTickerData->getDate()->getTimestamp() < 86400)) {
			return;
		}

		$fromDate = $firstDate;
		if ($lastTickerData !== null) {
			$fromDate = DateTimeImmutable::createFromRegular($lastTickerData->getDate());
		}

		$marketType = MarketTypeEnum::from($ticker->getMarket()->getType());
		match ($marketType) {
			MarketTypeEnum::Stock => $this->createTickerDataFromStock($ticker, $fromDate, $fullHistory),
			MarketTypeEnum::Crypto => $this->createTickerDataFromCrypto($ticker, $fromDate),
		};
	}

	private function createTickerDataFromStock(Ticker $ticker, DateTime|DateTimeImmutable $fromDate, bool $fullHistory = false): void
	{
		$dailyTimeSeries = $this->alphaVantageApiClient->getTimeSeriesDaily($ticker->getTicker(), $fullHistory);
		foreach ($dailyTimeSeries as $dailyTimeSerie) {
			if ($dailyTimeSerie->date < $fromDate) {
				continue;
			}

			$tickerData = new TickerData(
				ticker: $ticker,
				date: $dailyTimeSerie->date,
				open: (string) $dailyTimeSerie->open,
				close: (string) $dailyTimeSerie->close,
				high: (string) $dailyTimeSerie->high,
				low: (string) $dailyTimeSerie->low,
				volume: (string) $dailyTimeSerie->volume,
			);

			try {
				$this->tickerDataRepository->persist($tickerData);
			} catch (ConstrainException) {
				//ignore duplicate tickers
			}

			if ($dailyTimeSerie->splitCoefficient->toFloat() === 1.0) {
				continue;
			}

			$split = $this->splitRepository->findSplit($ticker->getId(), $dailyTimeSerie->date);
			if ($split !== null) {
				continue;
			}

			$split = new Split(ticker: $ticker, date: $dailyTimeSerie->date, factor: (string) $dailyTimeSerie->splitCoefficient);

			try {
				$this->splitRepository->persist($split);
			} catch (ConstrainException) {
				//ignore duplicate splits
			}
		}
	}

	private function createTickerDataFromCrypto(Ticker $ticker, DateTime|DateTimeImmutable $fromDate): void
	{
		$cryptoDailies = $this->alphaVantageApiClient->getCryptoDaily($ticker->getTicker());
		foreach ($cryptoDailies as $cryptoDaily) {
			if ($cryptoDaily->date < $fromDate) {
				continue;
			}

			$tickerData = new TickerData(
				ticker: $ticker,
				date: $cryptoDaily->date,
				open: (string) $cryptoDaily->open,
				close: (string) $cryptoDaily->close,
				high: (string) $cryptoDaily->high,
				low: (string) $cryptoDaily->low,
				volume: (string) $cryptoDaily->volume,
			);

			try {
				$this->tickerDataRepository->persist($tickerData);
			} catch (ConstrainException) {
				//ignore duplicate ticker data
			}
		}
	}
}
