<?php

declare(strict_types=1);

namespace FinGather\Service\Update;

use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\MarketTypeEnum;
use FinGather\Model\Entity\Enum\TickerTypeEnum;
use FinGather\Model\Entity\Market;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\TickerIndustry;
use FinGather\Model\Entity\TickerSector;
use FinGather\Model\Repository\CountryRepository;
use FinGather\Model\Repository\CurrencyRepository;
use FinGather\Model\Repository\MarketRepository;
use FinGather\Model\Repository\TickerIndustryRepository;
use FinGather\Model\Repository\TickerRepository;
use FinGather\Model\Repository\TickerSectorRepository;
use FinGather\Utils\StringUtils;
use MarekSkopal\TwelveData\Dto\Fundamentals\Profile;
use MarekSkopal\TwelveData\Exception\NotFoundException;
use MarekSkopal\TwelveData\TwelveData;

final class TickerUpdater
{
	public function __construct(
		private readonly TickerRepository $tickerRepository,
		private readonly MarketRepository $marketRepository,
		private readonly CurrencyRepository $currencyRepository,
		private readonly TickerIndustryRepository $tickerIndustryRepository,
		private readonly TickerSectorRepository $tickerSectorRepository,
		private readonly CountryRepository $countryRepository,
		private readonly TwelveData $twelveData,
	) {
	}

	public function updateTickers(): void
	{
		$markets = $this->marketRepository->findMarkets(type: MarketTypeEnum::Stock);
		foreach ($markets as $market) {
			$tickerTickers = $this->tickerRepository->findTickersTicker(marketId: $market->getId());

			$this->updateStockTickers($market, $tickerTickers);

			$this->updateEtfTickers($market, $tickerTickers);
		}

		$market = $this->marketRepository->findMarketByType(MarketTypeEnum::Crypto);
		assert($market instanceof Market);
		$currencyUsd = $this->currencyRepository->findCurrencyByCode('USD');
		assert($currencyUsd instanceof Currency);

		$tickerTickers = $this->tickerRepository->findTickersTicker(marketId: $market->getId());

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
				type: TickerTypeEnum::Crypto,
				isin: null,
				logo: null,
				sector: null,
				industry: null,
				website: null,
				description: null,
				country: null,
			);
			$this->tickerRepository->persist($ticker);
		}
	}

	/** @param list<string> $tickerTickers */
	private function updateStockTickers(Market $market, array $tickerTickers): void
	{
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

			$ticker = new Ticker(
				ticker: $stock->symbol,
				name: $stock->name,
				market: $market,
				currency: $currency,
				type: TickerTypeEnum::Stock,
				isin: null,
				logo: null,
				sector: null,
				industry: null,
				website: null,
				description: null,
				country: null,
			);
			$this->tickerRepository->persist($ticker);
		}
	}

	/** @param list<string> $tickerTickers */
	private function updateEtfTickers(Market $market, array $tickerTickers): void
	{
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

			$ticker = new Ticker(
				ticker: $etf->symbol,
				name: $etf->name,
				market: $market,
				currency: $currency,
				type: TickerTypeEnum::Etf,
				isin: null,
				logo: null,
				sector: null,
				industry: null,
				website: null,
				description: null,
				country: null,
			);
			$this->tickerRepository->persist($ticker);
		}
	}

	public function updateTicker(Ticker $ticker): void
	{
		if ($ticker->getMarket()->getType() === MarketTypeEnum::Crypto) {
			if ($ticker->getType() !== TickerTypeEnum::Crypto) {
				$ticker->setType(TickerTypeEnum::Crypto);
				$this->tickerRepository->persist($ticker);
			}

			return;
		}

		try {
			$profile = $this->twelveData->getFundamentals()->profile(
				symbol: $ticker->getTicker(),
				micCode: $ticker->getMarket()->getMic(),
			);
		} catch (NotFoundException) {
			return;
		}

		if ($profile->type === 'ETF' && $ticker->getType() !== TickerTypeEnum::Etf) {
			$ticker->setType(TickerTypeEnum::Etf);
		}

		$this->updateTickerSector($profile, $ticker);
		$this->updateTickerIndustry($profile, $ticker);
		$this->updateCountry($profile, $ticker);

		$ticker->setWebsite($profile->website);
		$ticker->setDescription($profile->description);

		$this->tickerRepository->persist($ticker);
	}

	private function updateTickerSector(Profile $profile, Ticker $ticker): void
	{
		if ($profile->sector === '') {
			$ticker->setSector(null);
		} else {
			$sectorName = StringUtils::sanitizeName($profile->sector);

			$tickerSector = $this->tickerSectorRepository->findTickerSectorByName($sectorName);
			if ($tickerSector === null) {
				$tickerSector = new TickerSector(name: $sectorName);
				$this->tickerSectorRepository->persist($tickerSector);
			}
			$ticker->setSector($tickerSector);
		}
	}

	private function updateTickerIndustry(Profile $profile, Ticker $ticker): void
	{
		if ($profile->industry === '') {
			$ticker->setIndustry(null);
		} else {
			$industryName = StringUtils::sanitizeName($profile->industry);

			$tickerIndustry = $this->tickerIndustryRepository->findTickerIndustryByName($industryName);
			if ($tickerIndustry === null) {
				$tickerIndustry = new TickerIndustry(name: $industryName);
				$this->tickerIndustryRepository->persist($tickerIndustry);
			}
			$ticker->setIndustry($tickerIndustry);
		}
	}

	private function updateCountry(Profile $profile, Ticker $ticker): void
	{
		if ($profile->country === '') {
			$ticker->setCountry(null);
		} else {
			$country = $this->countryRepository->findCountryByIsoCode($profile->country);
			if ($country === null) {
				$ticker->setCountry(null);
			}
			$ticker->setCountry($country);
		}
	}
}
