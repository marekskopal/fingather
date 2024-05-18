<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateInterval;
use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Cache\Cache;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\ExchangeRate;
use FinGather\Model\Repository\ExchangeRateRepository;
use MarekSkopal\TwelveData\Exception\NotFoundException;
use MarekSkopal\TwelveData\TwelveData;

class ExchangeRateProvider
{
	private readonly Cache $cache;

	public function __construct(private readonly ExchangeRateRepository $exchangeRateRepository, private readonly TwelveData $twelveData,)
	{
		$this->cache = new Cache(self::class);
	}

	public function getExchangeRate(DateTimeImmutable $date, Currency $currencyFrom, Currency $currencyTo): Decimal
	{
		$date = $date->setTime(0, 0);

		$key = $date->getTimestamp() . '_' . $currencyFrom->getCode() . '_' . $currencyTo->getCode();

		$exchangeRate = $this->cache->get($key);
		if ($exchangeRate instanceof Decimal) {
			return $exchangeRate;
		}

		if ($currencyFrom->getId() === $currencyTo->getId()) {
			$exchangeRate = new Decimal(1);
			$this->cache->set($key, $exchangeRate);

			return $exchangeRate;
		}

		$today = new \Safe\DateTimeImmutable('today');
		if ($date->getTimestamp() === $today->getTimestamp()) {
			$date = $date->sub(DateInterval::createFromDateString('1 day'));
		}

		$dayOfWeek = (int) $date->format('w');

		if ($dayOfWeek === 0) {
			$date = $date->sub(DateInterval::createFromDateString('2 days'));
		} elseif ($dayOfWeek === 6) {
			$date = $date->sub(DateInterval::createFromDateString('1 day'));
		}

		if ($currencyFrom->getCode() === 'USD') {
			$exchangeRate = $this->getExchangeRateUsd($date, $currencyTo);
			$this->cache->set($key, $exchangeRate);

			return $exchangeRate;
		}

		$exchangeRateFromUsd = $this->getExchangeRateUsd($date, $currencyFrom);
		$exchangeRateToUsd = $this->getExchangeRateUsd($date, $currencyTo);

		$exchangeRate = $exchangeRateToUsd->div($exchangeRateFromUsd);

		$this->cache->set($key, $exchangeRate);

		return $exchangeRate;
	}

	public function updateExchangeRates(Currency $currencyTo): ?DateTimeImmutable
	{
		$code = $currencyTo->getCode();
		$multiplier = 1;
		$multiplyCurrency = $currencyTo->getMultiplyCurrency();
		if ($multiplyCurrency !== null) {
			$code = $multiplyCurrency->getCode();
			$multiplier = $currencyTo->getMultiplier();
		}

		$lastExchangeRate = $this->exchangeRateRepository->findLastExchangeRate($currencyTo->getId());
		$startDate = $lastExchangeRate?->getDate() ?? new \Safe\DateTimeImmutable('2020-01-01');

		try {
			$timeSeries = $this->twelveData->getCoreData()->timeSeries(symbol: 'USD/' . $code, startDate: $startDate);
		} catch (NotFoundException) {
			return null;
		}

		foreach ($timeSeries->values as $timeSeriesValue) {
			$exchangeRate = new ExchangeRate(
				currency: $currencyTo,
				date: $timeSeriesValue->datetime,
				rate: (new Decimal($timeSeriesValue->close))->mul($multiplier),
			);
			$this->exchangeRateRepository->persist($exchangeRate);
		}

		return $startDate;
	}

	private function getExchangeRateUsd(DateTimeImmutable $date, Currency $currencyTo): Decimal
	{
		if ($currencyTo->getCode() === 'USD') {
			return new Decimal(1);
		}

		$exchangeRate = $this->exchangeRateRepository->findExchangeRate($date, $currencyTo->getId());
		if ($exchangeRate !== null) {
			return $exchangeRate->getRate();
		}

		$lastExchangeRate = $this->exchangeRateRepository->findLastExchangeRate($currencyTo->getId());
		assert($lastExchangeRate instanceof ExchangeRate);
		if ($date < $lastExchangeRate->getDate()) {
			$exchangeRate = $this->exchangeRateRepository->findNearestExchangeRate($date, $currencyTo->getId());
			if ($exchangeRate !== null) {
				return $exchangeRate->getRate();
			}
		}

		return $lastExchangeRate->getRate();
	}
}
