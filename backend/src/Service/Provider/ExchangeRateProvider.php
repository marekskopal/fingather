<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\ExchangeRate;
use FinGather\Model\Repository\ExchangeRateRepository;
use Safe\DateTime;

class ExchangeRateProvider
{
	public function __construct(private readonly ExchangeRateRepository $exchangeRateRepository)
	{
	}

	public function getExchangeRate(DateTime $date, Currency $currencyFrom, Currency $currencyTo): ExchangeRate
	{
		if ($currencyFrom->getCode() === 'USD') {
			return $this->getExchangeRateUsd($date, $currencyTo);
        }

		$exchangeRateFromUsd = $this->getExchangeRateUsd($date, $currencyFrom);
        $exchangeRateToUsd = $this->getExchangeRateUsd($date, $currencyTo);

        return new ExchangeRate(
			currency: $currencyTo,
			date: $date,
			rate: $exchangeRateFromUsd->getRate() / $exchangeRateToUsd->getRate()
		);
	}

	public function getExchangeRateUsd(DateTime $date, Currency $currencyTo): ExchangeRate
	{
		if ($currencyTo->getCode() === 'USD') {
			return new ExchangeRate(
				currency: $currencyTo,
				date: $date,
				rate: 1,
			);
        }

		$exchangeRate = $this->exchangeRateRepository->findExchangeRate($date, $currencyTo->getId());
		if ($exchangeRate !== null) {
			return $exchangeRate;
		}

		/**

		 *
		 * var historicalResponse = await _exchangeRateHostApiClient.GetHistorical(date);
		 *
		 * if (historicalResponse is not null && historicalResponse.Rates is not null)
		 * {
		 * CreateExchangeRateFromApi(date, "EUR", historicalResponse.Rates.Eur);
		 * CreateExchangeRateFromApi(date, "GBP", historicalResponse.Rates.Gbp);
		 * CreateExchangeRateFromApi(date, "GBX", historicalResponse.Rates.Gbp * 100);
		 * CreateExchangeRateFromApi(date, "CZK", historicalResponse.Rates.Czk);
		 * CreateExchangeRateFromApi(date, "CAD", historicalResponse.Rates.Cad);
		 * }
		 *
		 * if (date == DateTime.Today)
		 * {
		 * exchangeRate = await GetExchangeRateUsd(date.AddDays(-1), currency);
		 * }
		 * else
		 * {
		 * exchangeRate = _exchangeRateRepository.FindOne(er => er.Date == date && er.CurrencyId == currency.Id);
		 * }
		 *
		 * return exchangeRate;
		 * }
		 *
		 * private ExchangeRate CreateExchangeRateFromApi(DateTime date, string code, decimal rate)
		 * {
		 * var currency = _currencyRepository.FindCurrencyByCode(code);
		 *
		 * var exchangeRate = new ExchangeRate()
		 * {
		 * CurrencyId = currency.Id,
		 * Date = date,
		 * Rate = rate,
		 * };
		 * _exchangeRateRepository.Add(exchangeRate);
		 * _exchangeRateRepository.Commit();
		 *
		 * return exchangeRate;
		 * }
		 *
		 *
		 */
	}
}
