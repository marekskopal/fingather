<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateInterval;
use Decimal\Decimal;
use FinGather\Model\Entity\Split;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\TickerData;
use FinGather\Model\Repository\SplitRepository;
use FinGather\Model\Repository\TickerDataRepository;
use FinGather\Service\AlphaVantage\AlphaVantageApiClient;
use Safe\DateTime;

class TickerDataProvider
{
	public function __construct(
		private readonly TickerDataRepository $tickerDataRepository,
		private readonly SplitRepository $splitRepository,
		private readonly AlphaVantageApiClient $alphaVantageApiClient
	) {
	}

	public function getLastTickerData(Ticker $ticker, DateTime $beforeDate): ?TickerData
	{
		$dayOfWeek = (int) $beforeDate->format('w');

		if ($dayOfWeek === 6) {
			$beforeDate->sub(DateInterval::createFromDateString('2 days'));
		} elseif ($dayOfWeek === 5) {
			$beforeDate->sub(DateInterval::createFromDateString('1 day'));
		}

		$lastTickerData = $this->tickerDataRepository->findLastTickerData($ticker->getId(), $beforeDate);
		if ($lastTickerData !== null) {
			return $lastTickerData;
		}

		$this->createTickerData($ticker);

		return $this->tickerDataRepository->findLastTickerData($ticker->getId(), $beforeDate);
	}

	public function createTickerData(Ticker $ticker): void
	{
		$actualDate = new DateTime('today');

		$dayOfWeek = (int) $actualDate->format('w');

		if ($dayOfWeek === 6) {
			$actualDate->sub(DateInterval::createFromDateString('2 days'));
		} elseif ($dayOfWeek === 5) {
			$actualDate->sub(DateInterval::createFromDateString('1 day'));
		}

		$firstDate = (new DateTime('today'))->sub(DateInterval::createFromDateString('3 years'));

		$lastTickerData = $this->tickerDataRepository->findLastTickerData($ticker->getId());
		if ($lastTickerData !== null && ($actualDate->getTimestamp() - $lastTickerData->getDate()->getTimestamp() < 86400))	{
			return;
		}

		$fromDate = $firstDate;
		if ($lastTickerData !== null) {
			$fromDate = $lastTickerData->getDate();
		}

		$dailyTimeSeries = $this->alphaVantageApiClient->getTimeSeriesDaily($ticker->getTicker());
		$previousTickerData = $lastTickerData;
		foreach ($dailyTimeSeries as $dailyTimeSerie) {
			if ($dailyTimeSerie->date < $fromDate) {
				continue;
			}

			$performance = new Decimal('0.0');
			if ($previousTickerData !== null) {
				$performance = ($dailyTimeSerie->adjustedClose->div((new Decimal($previousTickerData->getClose()))->div(100)))->sub(100);
			}

			$tickerData = new TickerData(
				ticker: $ticker,
				date: $dailyTimeSerie->date,
				open: (string)$dailyTimeSerie->open,
				close: (string)$dailyTimeSerie->adjustedClose,
				high: (string)$dailyTimeSerie->high,
				low: (string)$dailyTimeSerie->low,
				volume: $dailyTimeSerie->volume,
				performance: $performance->toFloat(),
			);

			$this->tickerDataRepository->persist($tickerData);
			$previousTickerData = $tickerData;

			if ($dailyTimeSerie->splitCoefficient->toFloat() === 1.0) {
				continue;
			}

			$split = $this->splitRepository->findSplit($ticker->getId(), $dailyTimeSerie->date);
			if ($split !== null) {
				continue;
			}

			$split = new Split(ticker: $ticker, date: $dailyTimeSerie->date, factor: (string)$dailyTimeSerie->splitCoefficient);
			$this->splitRepository->persist($split);
		}
	}
}
