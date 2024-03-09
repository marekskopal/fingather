<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateInterval;
use Decimal\Decimal;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\ExchangeRate;
use FinGather\Model\Repository\ExchangeRateRepository;
use MarekSkopal\TwelveData\TwelveData;
use Safe\DateTimeImmutable;

class ExchangeRateProvider
{
	/** @var array<string, ExchangeRate> */
	private array $exchangeRates = [];

	public function __construct(private readonly ExchangeRateRepository $exchangeRateRepository, private readonly TwelveData $twelveData,)
	{
	}

	public function getExchangeRate(DateTimeImmutable $date, Currency $currencyFrom, Currency $currencyTo): ExchangeRate
	{
		$date = $date->setTime(0, 0);

		$key = $date->getTimestamp() . '_' . $currencyFrom->getCode() . '_' . $currencyTo->getCode();
		if (isset($this->exchangeRates[$key])) {
			return $this->exchangeRates[$key];
		}

		if ($currencyFrom->getId() === $currencyTo->getId()) {
			$this->exchangeRates[$key] = new ExchangeRate(currency: $currencyTo, date: $date, rate: new Decimal(1));

			return $this->exchangeRates[$key];
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

		if ($currencyFrom->getCode() === 'USD') {
			$this->exchangeRates[$key] = $this->getExchangeRateUsd($date, $currencyTo);
			return $this->exchangeRates[$key];
		}

		$exchangeRateFromUsd = $this->getExchangeRateUsd($date, $currencyFrom);
		$exchangeRateToUsd = $this->getExchangeRateUsd($date, $currencyTo);

		$exchangeRate = new ExchangeRate(
			currency: $currencyTo,
			date: $date,
			rate: $exchangeRateToUsd->getRate()->div($exchangeRateFromUsd->getRate()),
		);

		$this->exchangeRates[$key] = $exchangeRate;

		return $exchangeRate;
	}

	public function updateExchangeRates(Currency $currencyTo): void
	{
		$code = $currencyTo->getCode();
		$multiplier = 1;
		$multiplyCurrency = $currencyTo->getMultiplyCurrency();
		if ($multiplyCurrency !== null) {
			$code = $multiplyCurrency->getCode();
			$multiplier = $currencyTo->getMultiplier();
		}

		$lastExchangeRate = $this->exchangeRateRepository->findLastExchangeRate($currencyTo->getId());

		$timeSeries = $this->twelveData->getCoreData()->timeSeries(
			symbol: 'USD/' . $code,
			startDate: $lastExchangeRate?->getDate() ?? new DateTimeImmutable('2020-01-01'),
		);
		foreach ($timeSeries->values as $timeSeriesValue) {
			$exchangeRate = new ExchangeRate(
				currency: $currencyTo,
				date: $timeSeriesValue->datetime,
				rate: (new Decimal($timeSeriesValue->close))->mul($multiplier),
			);
			$this->exchangeRateRepository->persist($exchangeRate);
		}
	}

	private function getExchangeRateUsd(DateTimeImmutable $date, Currency $currencyTo): ExchangeRate
	{
		if ($currencyTo->getCode() === 'USD') {
			return new ExchangeRate(currency: $currencyTo, date: $date, rate: new Decimal(1));
		}

		$exchangeRate = $this->exchangeRateRepository->findExchangeRate($date, $currencyTo->getId());
		if ($exchangeRate !== null) {
			return $exchangeRate;
		}

		$lastExchangeRate = $this->exchangeRateRepository->findLastExchangeRate($currencyTo->getId());
		assert($lastExchangeRate instanceof ExchangeRate);
		if ($date < $lastExchangeRate->getDate()) {
			$exchangeRate = $this->exchangeRateRepository->findNearestExchangeRate($date, $currencyTo->getId());
			if ($exchangeRate !== null) {
				return $exchangeRate;
			}
		}

		return $lastExchangeRate;
	}
}
