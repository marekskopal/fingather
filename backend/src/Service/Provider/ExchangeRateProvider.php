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
	public function __construct(
		private readonly ExchangeRateRepository $exchangeRateRepository,
		private readonly AlphaVantageApiClient $alphaVantageApiClient
	)
	{
	}

	public function getExchangeRate(DateTimeImmutable $date, Currency $currencyFrom, Currency $currencyTo): ExchangeRate
	{
		if ($currencyFrom->getCode() === 'USD') {
			return $this->getExchangeRateUsd($date, $currencyTo);
		}

		$exchangeRateFromUsd = $this->getExchangeRateUsd($date, $currencyFrom);
		$exchangeRateToUsd = $this->getExchangeRateUsd($date, $currencyTo);

		return new ExchangeRate(
			currency: $currencyTo,
			date: $date,
			rate: (string)((new Decimal($exchangeRateFromUsd->getRate()))->div(new Decimal($exchangeRateToUsd->getRate())))
		);
	}

	public function getExchangeRateUsd(DateTimeImmutable $date, Currency $currencyTo): ExchangeRate
	{
		if ($currencyTo->getCode() === 'USD') {
			return new ExchangeRate(currency: $currencyTo, date: $date, rate: '1');
		}

		$today = new DateTimeImmutable('today');
		if ($date->getTimestamp() === $today->getTimestamp()) {
			$date = $date->sub(DateInterval::createFromDateString('1 day'));
		}

		$exchangeRate = $this->exchangeRateRepository->findExchangeRate($date, $currencyTo->getId());
		if ($exchangeRate !== null) {
			return $exchangeRate;
		}

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

			$exchangeRate = new ExchangeRate(currency: $currencyTo, date: $dailyResult->date, rate: (string)$dailyResult->close->mul($multiplier));
			$this->exchangeRateRepository->persist($exchangeRate);
		}

		$exchangeRate = $this->exchangeRateRepository->findExchangeRate($date, $currencyTo->getId());
		assert($exchangeRate instanceof ExchangeRate);
		return $exchangeRate;
	}
}
