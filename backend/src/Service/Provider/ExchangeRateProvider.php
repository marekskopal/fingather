<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateInterval;
use Decimal\Decimal;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\ExchangeRate;
use FinGather\Model\Repository\ExchangeRateRepository;
use FinGather\Service\AlphaVantage\AlphaVantageApiClient;
use Safe\DateTimeImmutable;

class ExchangeRateProvider
{
	/** @var array<string, ExchangeRate> */
	private array $exchangeRates = [];

	public function __construct(
		private readonly ExchangeRateRepository $exchangeRateRepository,
		private readonly AlphaVantageApiClient $alphaVantageApiClient
	) {
	}

	public function getExchangeRate(DateTimeImmutable $date, Currency $currencyFrom, Currency $currencyTo): ExchangeRate
	{
		$key = $date->getTimestamp() . '_' . $currencyFrom->getCode() . '_' . $currencyTo->getCode();
		if (isset($this->exchangeRates[$key])) {
			return $this->exchangeRates[$key];
		}

		$today = new DateTimeImmutable('today');
		if ($date->getTimestamp() === $today->getTimestamp()) {
			$date = $date->sub(DateInterval::createFromDateString('1 day'));
		}

		//TODO: AlphaVantage has not forex data for saturday and sunday. Find better source.
		$dayOfWeek = (int) $date->format('w');

		if ($dayOfWeek === 0) {
			$date = $date->sub(DateInterval::createFromDateString('2 days'));
		} elseif ($dayOfWeek === 6) {
			$date = $date->sub(DateInterval::createFromDateString('1 day'));
		}

		if ($currencyFrom->getCode() === 'USD') {
			return $this->getExchangeRateUsd($date, $currencyTo);
		}

		$exchangeRateFromUsd = $this->getExchangeRateUsd($date, $currencyFrom);
		$exchangeRateToUsd = $this->getExchangeRateUsd($date, $currencyTo);

		$exchangeRate = new ExchangeRate(
			currency: $currencyTo,
			date: $date,
			rate: (string) ((new Decimal($exchangeRateToUsd->getRate()))->div(new Decimal($exchangeRateFromUsd->getRate())))
		);

		$this->exchangeRates[$key] = $exchangeRate;

		return $exchangeRate;
	}

	public function updateExchangeRates(Currency $currencyTo): void
	{
		$code = $currencyTo->getCode();
		$multiplier = 1;
		if ($code === 'GBX') {
			$code = 'GBP';
			$multiplier = 100;
		}

		$lastExchangeRate = $this->exchangeRateRepository->findLastExchangeRate($currencyTo->getId());

		$fxDailyResults = $this->alphaVantageApiClient->getFxDaily($code);
		foreach ($fxDailyResults as $dailyResult) {
			if ($lastExchangeRate !== null && $dailyResult->date <= $lastExchangeRate->getDate()) {
				continue;
			}

			$exchangeRate = new ExchangeRate(
				currency: $currencyTo,
				date: $dailyResult->date,
				rate: (string) $dailyResult->close->mul($multiplier)
			);
			$this->exchangeRateRepository->persist($exchangeRate);
		}
	}

	private function getExchangeRateUsd(DateTimeImmutable $date, Currency $currencyTo): ExchangeRate
	{
		if ($currencyTo->getCode() === 'USD') {
			return new ExchangeRate(currency: $currencyTo, date: $date, rate: '1');
		}

		$exchangeRate = $this->exchangeRateRepository->findExchangeRate($date, $currencyTo->getId());
		if ($exchangeRate !== null) {
			return $exchangeRate;
		}

		$this->updateExchangeRates($currencyTo);

		$exchangeRate = $this->exchangeRateRepository->findLastExchangeRate($currencyTo->getId());
		assert($exchangeRate instanceof ExchangeRate);
		return $exchangeRate;
	}
}
