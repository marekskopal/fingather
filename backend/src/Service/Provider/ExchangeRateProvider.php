<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateInterval;
use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\ExchangeRate;
use FinGather\Model\Repository\ExchangeRateRepository;
use FinGather\Service\Cache\CacheFactory;
use FinGather\Utils\DateTimeUtils;
use MarekSkopal\TwelveData\Exception\BadRequestException;
use MarekSkopal\TwelveData\Exception\NotFoundException;
use MarekSkopal\TwelveData\TwelveData;
use Nette\Caching\Cache;

class ExchangeRateProvider
{
	private readonly Cache $cache;

	public function __construct(
		private readonly ExchangeRateRepository $exchangeRateRepository,
		private readonly TwelveData $twelveData,
		CacheFactory $cacheFactory,
	)
	{
		$this->cache = $cacheFactory->create(namespace: self::class);
	}

	public function getExchangeRate(DateTimeImmutable $date, Currency $currencyFrom, Currency $currencyTo): Decimal
	{
		$date = DateTimeUtils::setStartOfDateTime($date);

		$key = $date->getTimestamp() . '_' . $currencyFrom->code . '_' . $currencyTo->code;

		$exchangeRate = $this->cache->load($key);
		if ($exchangeRate instanceof Decimal) {
			return $exchangeRate;
		}

		if ($currencyFrom->id === $currencyTo->id) {
			$exchangeRate = new Decimal(1);
			$this->cache->save($key, $exchangeRate);

			return $exchangeRate;
		}

		$today = new DateTimeImmutable('today');
		if ($date->getTimestamp() === $today->getTimestamp()) {
			$date = $date->sub(DateInterval::createFromDateString('1 day'));
		}

		$dayOfWeek = (int) $date->format('w');

		if ($dayOfWeek === 0) {
			$date = $date->sub(DateInterval::createFromDateString('2 days'));
		} elseif ($dayOfWeek === 6) {
			$date = $date->sub(DateInterval::createFromDateString('1 day'));
		}

		if ($currencyFrom->code === 'USD') {
			$exchangeRate = $this->getExchangeRateUsd($date, $currencyTo);
			$this->cache->save($key, $exchangeRate);

			return $exchangeRate;
		}

		$exchangeRateFromUsd = $this->getExchangeRateUsd($date, $currencyFrom);
		$exchangeRateToUsd = $this->getExchangeRateUsd($date, $currencyTo);

		$exchangeRate = $exchangeRateToUsd->div($exchangeRateFromUsd);

		$this->cache->save($key, $exchangeRate);

		return $exchangeRate;
	}

	public function updateExchangeRates(Currency $currencyTo): ?DateTimeImmutable
	{
		$code = $currencyTo->code;
		$multiplier = 1;
		$multiplyCurrency = $currencyTo->multiplyCurrency;
		if ($multiplyCurrency !== null) {
			$code = $multiplyCurrency->code;
			$multiplier = $currencyTo->multiplier;
		}

		$lastExchangeRate = $this->exchangeRateRepository->findLastExchangeRate($currencyTo->id);
		$startDate = $lastExchangeRate?->date ?? new DateTimeImmutable('2020-01-01');

		try {
			$timeSeries = $this->twelveData->getCoreData()->timeSeries(symbol: 'USD/' . $code, startDate: $startDate);
		} catch (NotFoundException | BadRequestException) {
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
		if ($currencyTo->code === 'USD') {
			return new Decimal(1);
		}

		$exchangeRate = $this->exchangeRateRepository->findExchangeRate($date, $currencyTo->id);
		if ($exchangeRate !== null) {
			return $exchangeRate->rate;
		}

		$lastExchangeRate = $this->exchangeRateRepository->findLastExchangeRate($currencyTo->id);
		assert($lastExchangeRate instanceof ExchangeRate);
		if ($date < $lastExchangeRate->date) {
			$exchangeRate = $this->exchangeRateRepository->findNearestExchangeRate($date, $currencyTo->id);
			if ($exchangeRate !== null) {
				return $exchangeRate->rate;
			}
		}

		return $lastExchangeRate->rate;
	}
}
