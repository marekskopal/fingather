<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use Cycle\Database\Exception\StatementException\ConstrainException;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Enum\MarketTypeEnum;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\TickerData;
use FinGather\Model\Repository\TickerDataRepository;
use FinGather\Service\Cache\CacheFactory;
use FinGather\Service\Provider\Dto\TickerDataAdjustedDto;
use FinGather\Utils\DateTimeUtils;
use MarekSkopal\TwelveData\Dto\CoreData\TimeSeries;
use MarekSkopal\TwelveData\Enum\AdjustEnum;
use MarekSkopal\TwelveData\Exception\BadRequestException;
use MarekSkopal\TwelveData\Exception\NotFoundException;
use MarekSkopal\TwelveData\TwelveData;
use Nette\Caching\Cache;

class TickerDataProvider
{
	private const TwelveDataTimeSeriesMaxResults = 5000;

	private readonly Cache $cache;

	public function __construct(
		private readonly TickerDataRepository $tickerDataRepository,
		private readonly SplitProvider $splitProvider,
		private readonly TwelveData $twelveData,
		CacheFactory $cacheFactory,
	) {
		$this->cache = $cacheFactory->create(namespace: self::class);
	}

	/** @return \Iterator<TickerData> */
	public function getTickerDatas(Ticker $ticker, DateTimeImmutable $fromDate, DateTimeImmutable $toDate): \Iterator
	{
		return $this->tickerDataRepository->findTickerDatas($ticker->id, $fromDate, $toDate);
	}

	/** @return list<TickerDataAdjustedDto> */
	public function getAdjustedTickerDatas(Ticker $ticker, DateTimeImmutable $fromDate, DateTimeImmutable $toDate): array
	{
		$splits = $this->splitProvider->getSplits($ticker);

		return array_map(
			function (TickerData $tickerData) use ($splits): TickerDataAdjustedDto {
				$splitFactor = new Decimal(1);
				foreach ($splits as $split) {
					if ($split->date <= $tickerData->getDate()) {
						continue;
					}

					$splitFactor = $splitFactor->mul($split->factor);
				}

				return new TickerDataAdjustedDto(
					id: $tickerData->id,
					ticker: $tickerData->getTicker(),
					date: $tickerData->getDate(),
					open: $tickerData->getOpen()->div($splitFactor),
					close: $tickerData->getClose()->div($splitFactor),
					high: $tickerData->getHigh()->div($splitFactor),
					low: $tickerData->getLow()->div($splitFactor),
					volume: $tickerData->getVolume(),
				);
			},
			iterator_to_array($this->getTickerDatas($ticker, $fromDate, $toDate), false),
		);
	}

	public function getLastTickerDataClose(Ticker $ticker, DateTimeImmutable $beforeDate): ?Decimal
	{
		$dayOfWeek = (int) $beforeDate->format('w');

		if ($dayOfWeek === 0) {
			$beforeDate = $beforeDate->sub(DateInterval::createFromDateString('2 days'));
		} elseif ($dayOfWeek === 6) {
			$beforeDate = $beforeDate->sub(DateInterval::createFromDateString('1 day'));
		}

		$key = $ticker->id . '-' . $beforeDate->getTimestamp();
		/** @var Decimal|null $lastTickerDataClose */
		$lastTickerDataClose = $this->cache->load($key);
		if ($lastTickerDataClose !== null) {
			return $lastTickerDataClose;
		}

		$lastTickerData = $this->tickerDataRepository->findLastTickerData($ticker->id, $beforeDate);
		if ($lastTickerData !== null) {
			$this->cache->save($key, $lastTickerData->getClose());

			return $lastTickerData->getClose();
		}

		$this->updateTickerData($ticker, true);

		return $this->tickerDataRepository->findLastTickerData($ticker->id, $beforeDate)?->getClose();
	}

	public function updateTickerData(Ticker $ticker, bool $fullHistory = false): ?DateTimeImmutable
	{
		$lastTickerData = $this->tickerDataRepository->findLastTickerData($ticker->id);
		if ($lastTickerData !== null && $fullHistory) {
			return null;
		}

		$actualDate = new DateTime('today');

		$dayOfWeek = (int) $actualDate->format('w');

		if ($dayOfWeek === 0) {
			$actualDate->sub(DateInterval::createFromDateString('2 days'));
		} elseif ($dayOfWeek === 6) {
			$actualDate->sub(DateInterval::createFromDateString('1 day'));
		}

		$firstDate = new DateTimeImmutable(DateTimeUtils::FirstDate . ' 00:00:00');

		if ($lastTickerData !== null && ($actualDate->getTimestamp() - $lastTickerData->getDate()->getTimestamp() < 86400)) {
			return null;
		}

		$fromDate = $firstDate;
		if ($lastTickerData !== null) {
			$fromDate = $lastTickerData->getDate();
		}

		$marketType = $ticker->getMarket()->getType();
		$updatedTickerDataCount = match ($marketType) {
			MarketTypeEnum::Stock => $this->createTickerDataFromStock($ticker, $fromDate),
			MarketTypeEnum::Crypto => $this->createTickerDataFromCrypto($ticker, $fromDate),
		};

		if ($updatedTickerDataCount === 0) {
			return null;
		}

		return $fromDate;
	}

	private function createTickerDataFromStock(Ticker $ticker, DateTimeImmutable $fromDate, ?DateTimeImmutable $toDate = null): int
	{
		try {
			$timeSeries = $this->twelveData->getCoreData()->timeSeries(
				symbol: $ticker->getTicker(),
				micCode: $ticker->getMarket()->getMic(),
				startDate: $fromDate,
				endDate: $toDate,
				adjust: [AdjustEnum::None],
			);
		} catch (NotFoundException | BadRequestException) {
			return 0;
		}

		$this->createTickerData($ticker, $timeSeries);

		$valuesCount = count($timeSeries->values);

		if ($valuesCount === self::TwelveDataTimeSeriesMaxResults) {
			$this->createTickerDataFromStock($ticker, $fromDate, $timeSeries->values[4999]->datetime);
		}

		return $valuesCount;
	}

	private function createTickerDataFromCrypto(Ticker $ticker, DateTimeImmutable $fromDate, ?DateTimeImmutable $toDate = null): int
	{
		try {
			$timeSeries = $this->twelveData->getCoreData()->timeSeries(
				symbol: $ticker->getTicker() . '/USD',
				startDate: $fromDate,
				endDate: $toDate,
			);
			$this->createTickerData($ticker, $timeSeries);
		} catch (NotFoundException | BadRequestException) {
			return 0;
		}

		$valuesCount = count($timeSeries->values);

		if ($valuesCount === self::TwelveDataTimeSeriesMaxResults) {
			$this->createTickerDataFromCrypto($ticker, $fromDate, $timeSeries->values[4999]->datetime);
		}

		return $valuesCount;
	}

	private function createTickerData(Ticker $ticker, TimeSeries $timeSeries): void
	{
		foreach ($timeSeries->values as $timeSeriesValue) {
			$tickerData = new TickerData(
				ticker: $ticker,
				date: $timeSeriesValue->datetime,
				open: new Decimal($timeSeriesValue->open),
				close: new Decimal($timeSeriesValue->close),
				high: new Decimal($timeSeriesValue->high),
				low: new Decimal($timeSeriesValue->low),
				volume: new Decimal($timeSeriesValue->volume ?? 0),
			);

			try {
				$this->tickerDataRepository->persist($tickerData);
			} catch (ConstrainException) {
				//ignore duplicate tickers
			}

			$key = $ticker->id . '-' . $timeSeriesValue->datetime->getTimestamp();
			$this->cache->remove($key);
		}
	}
}
