<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use AlphaVantage\Exception\RuntimeException;
use FinGather\Model\Entity\Enum\MarketTypeEnum;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Repository\CurrencyRepository;
use FinGather\Model\Repository\MarketRepository;
use FinGather\Model\Repository\TickerRepository;
use FinGather\Service\AlphaVantage\AlphaVantageApiClient;
use MarekSkopal\TwelveData\TwelveData;

class TickerProvider
{
	public function __construct(
		private readonly TickerRepository $tickerRepository,
		private readonly MarketRepository $marketRepository,
		private readonly CurrencyRepository $currencyRepository,
		private readonly AlphaVantageApiClient $alphaVantageApiClient,
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

	public function getOrCreateTicker(string $ticker): ?Ticker
	{
		$tickerEntity = $this->tickerRepository->findTickerByTicker($ticker);
		if ($tickerEntity !== null) {
			return $tickerEntity;
		}

		$tickerParts = explode('.', $ticker);
		$marketMic = end($tickerParts);

		$market = match (count($tickerParts)) {
			1 => $this->marketRepository->findMarketByType(MarketTypeEnum::Crypto),
			2 => $this->marketRepository->findMarketByMic($marketMic),
			default => null,
		};
		if ($market === null) {
			return null;
		}

		$type = MarketTypeEnum::from($market->getType());

		switch ($type) {
			case MarketTypeEnum::Stock:
				$apiTicker = $this->alphaVantageApiClient->tickerSearch($ticker);
				if ($apiTicker === null) {
					return null;
				}

				$currency = $this->currencyRepository->findCurrencyByCode($apiTicker->currency);
				if ($currency === null) {
					return null;
				}

				$name = $apiTicker->name;

				break;

			case MarketTypeEnum::Crypto:
				try {
					$this->alphaVantageApiClient->getCryptoDaily($ticker);
				} catch (RuntimeException) {
					return null;
				}

				$currency = $market->getCurrency();
				$name = $ticker;

				break;
		}

		$ticker = new Ticker(ticker: $ticker, name: $name, market: $market, currency: $currency);
		$this->tickerRepository->persist($ticker);

		return $ticker;
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

				$currency = $this->currencyRepository->findCurrencyByCode($stock->currency);
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

				$currency = $this->currencyRepository->findCurrencyByCode($etf->currency);
				if ($currency === null) {
					continue;
				}

				$ticker = new Ticker(ticker: $etf->symbol, name: $etf->name, market: $market, currency: $currency);
				$this->tickerRepository->persist($ticker);
			}
		}
	}
}
