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
use MarekSkopal\TwelveData\Exception\NotFoundException;
use MarekSkopal\TwelveData\TwelveData;
use Safe\Exceptions\FilesystemException;
use function Safe\file_get_contents;
use function Safe\file_put_contents;
use function Safe\mkdir;

class TickerProvider
{
	private const string LOGOS_PATH = '/app/public/images/logos/';
	private const string LOGOS_API_DIR = 'api/';

	public function __construct(
		private readonly TickerRepository $tickerRepository,
		private readonly MarketRepository $marketRepository,
		private readonly CurrencyRepository $currencyRepository,
		private readonly TwelveData $twelveData,
	) {
	}

	/** @return array<Ticker> */
	public function getTickers(?Market $market = null, ?string $search = null, ?int $limit = null, ?int $offset = null,): array
	{
		return $this->tickerRepository->findTickers(marketId: $market?->getId(), search: $search, limit: $limit, offset: $offset);
	}

	public function getTicker(int $tickerId): ?Ticker
	{
		return $this->tickerRepository->findTicker($tickerId);
	}

	/** @return array<Ticker> */
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

	public function getTickerByTicker(string $ticker, ?Market $market = null): ?Ticker
	{
		return $this->tickerRepository->findTickerByTicker($ticker, $market?->getId());
	}

	public function updateTickers(): void
	{
		$markets = $this->marketRepository->findMarkets(type: MarketTypeEnum::Stock);
		foreach ($markets as $market) {
			$tickers = $this->tickerRepository->findTickers(marketId: $market->getId());
			$tickerTickers = array_map(fn(Ticker $ticker): string => $ticker->getTicker(), $tickers);

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

				$ticker = new Ticker(ticker: $stock->symbol, name: $stock->name, market: $market, currency: $currency, logo: null);
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

				$ticker = new Ticker(ticker: $etf->symbol, name: $etf->name, market: $market, currency: $currency, logo: null);
				$this->tickerRepository->persist($ticker);
			}
		}

		$market = $this->marketRepository->findMarketByType(MarketTypeEnum::Crypto);
		assert($market instanceof Market);
		$currencyUsd = $this->currencyRepository->findCurrencyByCode('USD');
		assert($currencyUsd instanceof Currency);

		$tickers = $this->tickerRepository->findTickers(marketId: $market->getId());
		$tickerTickers = array_map(fn(Ticker $ticker): string => $ticker->getTicker(), $tickers);

		$cryptocurrenciesList = $this->twelveData->getReferenceData()->cryptocurrenciesList(exchange: 'Binance');
		foreach ($cryptocurrenciesList->data as $cryptocurrency) {
			if (!str_ends_with($cryptocurrency->symbol, '/USD')) {
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
				logo: null,
			);
			$this->tickerRepository->persist($ticker);
		}
	}

	public function updateTickerLogo(Ticker $ticker): void
	{
		$currentLogo = $ticker->getLogo();
		if ($currentLogo !== null && !str_starts_with($currentLogo, self::LOGOS_API_DIR)) {
			return;
		}

		// If the logo is in the filesystem, we use them
		if (file_exists(self::LOGOS_PATH . $ticker->getTicker() . '.svg')) {
			$ticker->setLogo($ticker->getTicker() . '.svg');
			$this->tickerRepository->persist($ticker);
			return;
		}

		// If the logo is not in the filesystem, we download it from API
		try {
			$logo = $ticker->getMarket()->getType() === MarketTypeEnum::Crypto->value ? $this->twelveData->getFundamentals()->logo(
				symbol: $ticker->getTicker() . '/USD',
			) : $this->twelveData->getFundamentals()->logo(
				symbol: $ticker->getTicker(),
				micCode: $ticker->getMarket()->getMic(),
			);
		} catch (NotFoundException) {
			return;
		}

		if (($logo->url === null || $logo->url === '') && ($logo->logoBase === null || $logo->logoBase === '')) {
			return;
		}

		if ($logo->url !== null && $logo->url !== '') {
			$url = $logo->url;
		} elseif ($logo->logoBase !== null && $logo->logoBase !== '') {
			$url = $logo->logoBase;
		} else {
			return;
		}

		try {
			$fileContents = file_get_contents($url);
		} catch (FilesystemException) {
			return;
		}

		if (!is_dir(self::LOGOS_PATH . self::LOGOS_API_DIR)) {
			mkdir(self::LOGOS_PATH . self::LOGOS_API_DIR, recursive: true);
		}

		$filename = strtolower($ticker->getMarket()->getMic() . '-' . $ticker->getTicker()) . '.png';
		file_put_contents(self::LOGOS_PATH . self::LOGOS_API_DIR . $filename, $fileContents);

		$ticker->setLogo(self::LOGOS_API_DIR . $filename);
		$this->tickerRepository->persist($ticker);
	}
}
