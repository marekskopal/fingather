<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\MarketTypeEnum;
use FinGather\Model\Entity\Market;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Repository\CurrencyRepository;
use FinGather\Model\Repository\MarketRepository;
use FinGather\Model\Repository\TickerRepository;
use MarekSkopal\TwelveData\TwelveData;

class TickerProvider
{
	public function __construct(
		private readonly TickerRepository $tickerRepository,
		private readonly MarketRepository $marketRepository,
		private readonly CurrencyRepository $currencyRepository,
		private readonly TwelveData $twelveData,
	) {
	}

	/** @return array<Ticker> */
	public function getTickers(?string $search = null, ?int $limit = null, ?int $offset = null,): array
	{
		return $this->tickerRepository->findTickers($search, $limit, $offset);
	}

	public function getTicker(int $tickerId): ?Ticker
	{
		return $this->tickerRepository->findTicker($tickerId);
	}

	public function getActiveTickers(): array
	{
		return $this->tickerRepository->findActiveTickers();
	}

	/** @return array<Ticker> */
	public function getTickersByTicker(string $ticker): array
	{
		return $this->tickerRepository->findTickersByTicker($ticker);
	}

	public function countTickersByTicker(string $ticker): int
	{
		return $this->tickerRepository->countTickersByTicker($ticker);
	}

	public function getTickerByTicker(string $ticker): ?Ticker
	{
		return $this->tickerRepository->findTickerByTicker($ticker);
	}

	public function updateTickers(): void
	{
		$tickers = $this->tickerRepository->findTickers();
		$tickerTickers = array_map(fn(Ticker $ticker): string => $ticker->getTicker(), $tickers);

		$markets = $this->marketRepository->findMarkets(type: MarketTypeEnum::Stock);
		foreach ($markets as $market) {
			$stockList = $this->twelveData->getReferenceData()->stockList(micCode: $market->getMic());
			foreach ($stockList->data as $stock) {
				if (in_array($stock->symbol, $tickerTickers, true)) {
					continue;
				}

				$stockCurrency = $stock->currency;
				if ($stockCurrency === 'GBp') {
					$stockCurrency = 'GBX';
				}

				$currency = $this->currencyRepository->findCurrencyByCode($stockCurrency);
				if ($currency === null) {
					continue;
				}

				$ticker = new Ticker(ticker: $stock->symbol, name: $stock->name, market: $market, currency: $currency);
				$this->tickerRepository->persist($ticker);
			}

			$etfList = $this->twelveData->getReferenceData()->etfList(micCode: $market->getMic());
			foreach ($etfList->data as $etf) {
				if (in_array($etf->symbol, $tickerTickers, true)) {
					continue;
				}

				$etfCurrency = $etf->currency;
				if ($etfCurrency === 'GBp') {
					$etfCurrency = 'GBX';
				}

				$currency = $this->currencyRepository->findCurrencyByCode($etfCurrency);
				if ($currency === null) {
					continue;
				}

				$ticker = new Ticker(ticker: $etf->symbol, name: $etf->name, market: $market, currency: $currency);
				$this->tickerRepository->persist($ticker);
			}
		}

		$market = $this->marketRepository->findMarketByType(MarketTypeEnum::Crypto);
		assert($market instanceof Market);
		$currencyUsd = $this->currencyRepository->findCurrencyByCode('USD');
		assert($currencyUsd instanceof Currency);

		$cryptocurrenciesList = $this->twelveData->getReferenceData()->cryptocurrenciesList(exchange: 'Binance');
		foreach ($cryptocurrenciesList->data as $cryptocurrency) {
			if (str_ends_with($cryptocurrency->symbol, '/USD')) {
				continue;
			}

			$cryptocurrencySymbol = str_replace('/USD', '', $cryptocurrency->symbol);

			if (in_array($cryptocurrencySymbol, $tickerTickers, true)) {
				continue;
			}

			$ticker = new Ticker(
				ticker: $cryptocurrencySymbol,
				name: $cryptocurrency->currencyBase,
				market: $market,
				currency: $currencyUsd,
			);
			$this->tickerRepository->persist($ticker);
		}
	}
}
