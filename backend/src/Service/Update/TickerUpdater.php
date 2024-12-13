<?php

declare(strict_types=1);

namespace FinGather\Service\Update;

use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\MarketTypeEnum;
use FinGather\Model\Entity\Enum\TickerTypeEnum;
use FinGather\Model\Entity\Industry;
use FinGather\Model\Entity\Market;
use FinGather\Model\Entity\Sector;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Repository\CountryRepository;
use FinGather\Model\Repository\CurrencyRepository;
use FinGather\Model\Repository\IndustryRepository;
use FinGather\Model\Repository\MarketRepository;
use FinGather\Model\Repository\SectorRepository;
use FinGather\Model\Repository\TickerRepository;
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
		private readonly IndustryRepository $industryRepository,
		private readonly SectorRepository $sectorRepository,
		private readonly CountryRepository $countryRepository,
		private readonly TwelveData $twelveData,
	) {
	}

	public function updateTickers(): void
	{
		$markets = $this->marketRepository->findMarkets(type: MarketTypeEnum::Stock);
		foreach ($markets as $market) {
			$tickerTickers = $this->tickerRepository->findTickersTicker(marketId: $market->id);

			$this->updateStockTickers($market, $tickerTickers);

			$this->updateEtfTickers($market, $tickerTickers);
		}

		$market = $this->marketRepository->findMarketByType(MarketTypeEnum::Crypto);
		assert($market instanceof Market);
		$currencyUsd = $this->currencyRepository->findCurrencyByCode('USD');
		assert($currencyUsd instanceof Currency);

		$tickerTickers = $this->tickerRepository->findTickersTicker(marketId: $market->id);

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
				sector: $this->sectorRepository->findOthersSector(),
				industry: $this->industryRepository->findOthersIndustry(),
				website: null,
				description: null,
				country: $this->countryRepository->findOthersCountry(),
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
				sector: $this->sectorRepository->findOthersSector(),
				industry: $this->industryRepository->findOthersIndustry(),
				website: null,
				description: null,
				country: $this->countryRepository->findOthersCountry(),
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
				sector: $this->sectorRepository->findOthersSector(),
				industry: $this->industryRepository->findOthersIndustry(),
				website: null,
				description: null,
				country: $this->countryRepository->findOthersCountry(),
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

		$this->updateSector($profile, $ticker);
		$this->updateIndustry($profile, $ticker);
		$this->updateCountry($profile, $ticker);

		$ticker->setWebsite($profile->website);
		$ticker->setDescription($profile->description);

		$this->tickerRepository->persist($ticker);
	}

	private function updateSector(Profile $profile, Ticker $ticker): void
	{
		if ($profile->sector === '') {
			$othersSector = $this->sectorRepository->findOthersSector();
			$ticker->setSector($othersSector);
			return;
		}

		$sectorName = StringUtils::sanitizeName($profile->sector);

		$sector = $this->sectorRepository->findSectorByName($sectorName);
		if ($sector === null) {
			$sector = new Sector(name: $sectorName, isOthers: false);
			$this->sectorRepository->persist($sector);
		}
		$ticker->setSector($sector);
	}

	private function updateIndustry(Profile $profile, Ticker $ticker): void
	{
		if ($profile->industry === '') {
			$othersIndustry = $this->industryRepository->findOthersIndustry();
			$ticker->setIndustry($othersIndustry);
			return;
		}

		$industryName = StringUtils::sanitizeName($profile->industry);

		$industry = $this->industryRepository->findIndustryByName($industryName);
		if ($industry === null) {
			$industry = new Industry(name: $industryName, isOthers: false);
			$this->industryRepository->persist($industry);
		}
		$ticker->setIndustry($industry);
	}

	private function updateCountry(Profile $profile, Ticker $ticker): void
	{
		if ($profile->country === '') {
			$othersCountry = $this->countryRepository->findOthersCountry();
			$ticker->setCountry($othersCountry);
			return;
		}

		$country = $this->countryRepository->findCountryByName($profile->country);
		if ($country === null) {
			$othersCountry = $this->countryRepository->findOthersCountry();
			$ticker->setCountry($othersCountry);
			return;
		}

		$ticker->setCountry($country);
	}
}
