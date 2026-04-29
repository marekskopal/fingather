<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Provider;

use ArrayIterator;
use DateTimeImmutable;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Country;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Industry;
use FinGather\Model\Entity\Market;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Sector;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\User;
use FinGather\Service\Provider\AssetProviderInterface;
use FinGather\Service\Provider\CountryProvider;
use FinGather\Service\Provider\IndustryProvider;
use FinGather\Service\Provider\SectorProvider;
use FinGather\Tests\Fixtures\Model\Entity\AssetFixture;
use FinGather\Tests\Fixtures\Model\Entity\CountryFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\TickerFixture;
use FinGather\Tests\Fixtures\Model\Entity\TickerIndustryFixture;
use FinGather\Tests\Fixtures\Model\Entity\TickerSectorFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CountryProvider::class)]
#[CoversClass(SectorProvider::class)]
#[CoversClass(IndustryProvider::class)]
#[UsesClass(Asset::class)]
#[UsesClass(Country::class)]
#[UsesClass(Currency::class)]
#[UsesClass(Group::class)]
#[UsesClass(Industry::class)]
#[UsesClass(Market::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(Sector::class)]
#[UsesClass(Ticker::class)]
#[UsesClass(User::class)]
final class AssetGroupingProvidersTest extends TestCase
{
	public function testCountryProviderDedupsCountriesAcrossAssets(): void
	{
		$us = CountryFixture::getCountry(id: 1, name: 'United States');
		$de = CountryFixture::getCountry(id: 2, name: 'Germany');

		$assets = [
			$this->makeAssetWithCountry(1, $us),
			$this->makeAssetWithCountry(2, $de),
			$this->makeAssetWithCountry(3, $us),
		];

		$assetProvider = self::createStub(AssetProviderInterface::class);
		$assetProvider->method('getAssets')->willReturn(new ArrayIterator($assets));

		$provider = new CountryProvider($assetProvider);

		$countries = $provider->getCountriesFromAssets(UserFixture::getUser(), PortfolioFixture::getPortfolio(), new DateTimeImmutable());

		self::assertCount(2, $countries);
		self::assertSame('United States', $countries[1]->name);
		self::assertSame('Germany', $countries[2]->name);
	}

	public function testSectorProviderDedupsSectorsAcrossAssets(): void
	{
		$tech = TickerSectorFixture::getTickerSector(id: 1, name: 'Technology');
		$health = TickerSectorFixture::getTickerSector(id: 2, name: 'Healthcare');

		$assets = [
			$this->makeAssetWithSector(1, $tech),
			$this->makeAssetWithSector(2, $tech),
			$this->makeAssetWithSector(3, $health),
		];

		$assetProvider = self::createStub(AssetProviderInterface::class);
		$assetProvider->method('getAssets')->willReturn(new ArrayIterator($assets));

		$provider = new SectorProvider($assetProvider);

		$sectors = $provider->getSectorsFromAssets(UserFixture::getUser(), PortfolioFixture::getPortfolio(), new DateTimeImmutable());

		self::assertCount(2, $sectors);
		self::assertSame('Technology', $sectors[1]->name);
		self::assertSame('Healthcare', $sectors[2]->name);
	}

	public function testIndustryProviderDedupsIndustriesAcrossAssets(): void
	{
		$semis = TickerIndustryFixture::getTickerIndustry(id: 1, name: 'Semiconductors');
		$banks = TickerIndustryFixture::getTickerIndustry(id: 2, name: 'Banks');

		$assets = [
			$this->makeAssetWithIndustry(1, $semis),
			$this->makeAssetWithIndustry(2, $banks),
			$this->makeAssetWithIndustry(3, $semis),
			$this->makeAssetWithIndustry(4, $banks),
		];

		$assetProvider = self::createStub(AssetProviderInterface::class);
		$assetProvider->method('getAssets')->willReturn(new ArrayIterator($assets));

		$provider = new IndustryProvider($assetProvider);

		$industries = $provider->getIndustriesFromAssets(UserFixture::getUser(), PortfolioFixture::getPortfolio(), new DateTimeImmutable());

		self::assertCount(2, $industries);
		self::assertSame('Semiconductors', $industries[1]->name);
		self::assertSame('Banks', $industries[2]->name);
	}

	public function testCountryProviderReturnsEmptyArrayForNoAssets(): void
	{
		$assetProvider = self::createStub(AssetProviderInterface::class);
		$assetProvider->method('getAssets')->willReturn(new ArrayIterator([]));

		$provider = new CountryProvider($assetProvider);

		$countries = $provider->getCountriesFromAssets(UserFixture::getUser(), PortfolioFixture::getPortfolio(), new DateTimeImmutable());

		self::assertSame([], $countries);
	}

	private function makeAssetWithCountry(int $id, Country $country): Asset
	{
		$ticker = TickerFixture::getTicker(id: $id, country: $country);
		return AssetFixture::getAsset(id: $id, ticker: $ticker);
	}

	private function makeAssetWithSector(int $id, Sector $sector): Asset
	{
		$ticker = TickerFixture::getTicker(id: $id, sector: $sector);
		return AssetFixture::getAsset(id: $id, ticker: $ticker);
	}

	private function makeAssetWithIndustry(int $id, Industry $industry): Asset
	{
		$ticker = TickerFixture::getTicker(id: $id, industry: $industry);
		return AssetFixture::getAsset(id: $id, ticker: $ticker);
	}
}
