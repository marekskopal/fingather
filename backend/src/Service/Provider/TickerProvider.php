<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Ticker;
use FinGather\Model\Repository\CurrencyRepository;
use FinGather\Model\Repository\MarketRepository;
use FinGather\Model\Repository\TickerRepository;
use FinGather\Service\AlphaVantage\AlphaVantageApiClient;

class TickerProvider
{
	public function __construct(
		private readonly TickerRepository $tickerRepository,
		private readonly MarketRepository $marketRepository,
		private readonly CurrencyRepository $currencyRepository,
		private readonly AlphaVantageApiClient $alphaVantageApiClient,
	) {
	}

	public function getOrCreateTicker(string $ticker): ?Ticker
	{
		$ticker = $this->tickerRepository->findTickerByTicker($ticker);
		if ($ticker !== null) {
			return $ticker;
		}

		$apiTicker = $this->alphaVantageApiClient->tickerSearch($ticker);
		if ($apiTicker === null) {
			return null;
		}

		$tickerParts = explode('.', $ticker);
		$marketMic = end($tickerParts);
		$market = $this->marketRepository->findMarkerByMic($marketMic);
		if ($market === null) {
			return null;
		}

		$currency = $this->currencyRepository->findCurrencyByCode($apiTicker->currency);
		if ($currency === null) {
			return null;
		}

		$ticker = new Ticker(ticker: $apiTicker->symbol, name: $apiTicker->name, market: $market, currency: $currency);
		$this->tickerRepository->persist($ticker);

		return $ticker;
	}
}
