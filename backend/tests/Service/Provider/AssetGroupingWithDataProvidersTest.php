<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Provider;

use ArrayIterator;
use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Dto\AbstractGroupWithGroupDataDto;
use FinGather\Dto\AssetWithPropertiesDto;
use FinGather\Dto\CountryDto;
use FinGather\Dto\GroupDataDto;
use FinGather\Dto\GroupWithGroupDataDto;
use FinGather\Dto\IndustryDto;
use FinGather\Dto\IndustryWithIndustryDataDto;
use FinGather\Dto\MarketDto;
use FinGather\Dto\SectorDto;
use FinGather\Dto\SectorWithSectorDataDto;
use FinGather\Dto\TickerDto;
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
use FinGather\Service\DataCalculator\Dto\AssetDataDto;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use FinGather\Service\Provider\AssetDataProviderInterface;
use FinGather\Service\Provider\AssetProviderInterface;
use FinGather\Service\Provider\GroupDataProviderInterface;
use FinGather\Service\Provider\GroupWithGroupDataProvider;
use FinGather\Service\Provider\IndustryDataProviderInterface;
use FinGather\Service\Provider\IndustryProviderInterface;
use FinGather\Service\Provider\IndustryWithIndustryDataProvider;
use FinGather\Service\Provider\PortfolioDataProviderInterface;
use FinGather\Service\Provider\SectorDataProviderInterface;
use FinGather\Service\Provider\SectorProviderInterface;
use FinGather\Service\Provider\SectorWithSectorDataProvider;
use FinGather\Tests\Fixtures\Model\Entity\AssetFixture;
use FinGather\Tests\Fixtures\Model\Entity\GroupFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\TickerFixture;
use FinGather\Tests\Fixtures\Model\Entity\TickerIndustryFixture;
use FinGather\Tests\Fixtures\Model\Entity\TickerSectorFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use FinGather\Utils\CalculatorUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SectorWithSectorDataProvider::class)]
#[CoversClass(IndustryWithIndustryDataProvider::class)]
#[CoversClass(GroupWithGroupDataProvider::class)]
#[UsesClass(AbstractGroupWithGroupDataDto::class)]
#[UsesClass(AssetWithPropertiesDto::class)]
#[UsesClass(CountryDto::class)]
#[UsesClass(GroupDataDto::class)]
#[UsesClass(GroupWithGroupDataDto::class)]
#[UsesClass(IndustryDto::class)]
#[UsesClass(IndustryWithIndustryDataDto::class)]
#[UsesClass(MarketDto::class)]
#[UsesClass(SectorDto::class)]
#[UsesClass(SectorWithSectorDataDto::class)]
#[UsesClass(TickerDto::class)]
#[UsesClass(Asset::class)]
#[UsesClass(AssetDataDto::class)]
#[UsesClass(CalculatedDataDto::class)]
#[UsesClass(CalculatorUtils::class)]
#[UsesClass(Country::class)]
#[UsesClass(Currency::class)]
#[UsesClass(Group::class)]
#[UsesClass(Industry::class)]
#[UsesClass(Market::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(Sector::class)]
#[UsesClass(Ticker::class)]
#[UsesClass(User::class)]
final class AssetGroupingWithDataProvidersTest extends TestCase
{
	public function testSectorWithSectorDataDropsSectorsWithZeroValue(): void
	{
		$tech = TickerSectorFixture::getTickerSector(id: 1, name: 'Technology');
		$health = TickerSectorFixture::getTickerSector(id: 2, name: 'Healthcare');

		$sectorProvider = self::createStub(SectorProviderInterface::class);
		$sectorProvider->method('getSectorsFromAssets')->willReturn([1 => $tech, 2 => $health]);

		// Tech has 600 of 1000 → 60%; Healthcare has 0 → skipped.
		$sectorDataProvider = self::createStub(SectorDataProviderInterface::class);
		$sectorDataProvider->method('getSectorData')->willReturnCallback(
			fn (Sector $sector): CalculatedDataDto => $this->makeCalculatedDataDto(
				$sector->id === 1 ? new Decimal('600') : new Decimal('0'),
			),
		);

		$portfolioDataProvider = self::createStub(PortfolioDataProviderInterface::class);
		$portfolioDataProvider->method('getPortfolioData')->willReturn($this->makeCalculatedDataDto(new Decimal('1000')));

		$provider = new SectorWithSectorDataProvider(
			portfolioDataProvider: $portfolioDataProvider,
			sectorProvider: $sectorProvider,
			sectorDataProvider: $sectorDataProvider,
		);

		$result = $provider->getSectorsWithSectorData(UserFixture::getUser(), PortfolioFixture::getPortfolio(), new DateTimeImmutable());

		self::assertCount(1, $result);
		self::assertSame('Technology', $result[0]->name);
		self::assertSame(60.0, $result[0]->percentage);
	}

	public function testIndustryWithIndustryDataReportsPercentages(): void
	{
		$semis = TickerIndustryFixture::getTickerIndustry(id: 1, name: 'Semiconductors');

		$industryProvider = self::createStub(IndustryProviderInterface::class);
		$industryProvider->method('getIndustriesFromAssets')->willReturn([1 => $semis]);

		$industryDataProvider = self::createStub(IndustryDataProviderInterface::class);
		$industryDataProvider->method('getIndustryData')->willReturn($this->makeCalculatedDataDto(new Decimal('250')));

		$portfolioDataProvider = self::createStub(PortfolioDataProviderInterface::class);
		$portfolioDataProvider->method('getPortfolioData')->willReturn($this->makeCalculatedDataDto(new Decimal('1000')));

		$provider = new IndustryWithIndustryDataProvider(
			portfolioDataProvider: $portfolioDataProvider,
			industryProvider: $industryProvider,
			industryDataProvider: $industryDataProvider,
		);

		$result = $provider->getIndustriesWithIndustryData(
			UserFixture::getUser(),
			PortfolioFixture::getPortfolio(),
			new DateTimeImmutable(),
		);

		self::assertCount(1, $result);
		self::assertSame('Semiconductors', $result[0]->name);
		self::assertSame(25.0, $result[0]->percentage);
	}

	public function testGroupWithGroupDataPartitionsAssetsByGroupAndCalculatesPercentages(): void
	{
		// Group 1 holds asset 1 (open value 600) and asset 3 (open value 200) → 80%
		// Group 2 holds asset 2 (open value 200) → 20%
		// Asset 4 is closed → excluded
		$group1 = GroupFixture::getGroup(name: 'Tech', color: '#aaa');
		$group1->id = 1;
		$group2 = GroupFixture::getGroup(name: 'Bonds', color: '#bbb');
		$group2->id = 2;

		$assets = [
			$this->makeAssetWithGroup(1, 'AAPL', $group1),
			$this->makeAssetWithGroup(2, 'BND', $group2),
			$this->makeAssetWithGroup(3, 'MSFT', $group1),
			$this->makeAssetWithGroup(4, 'CLOSED', $group1),
		];

		$assetProvider = self::createStub(AssetProviderInterface::class);
		$assetProvider->method('getAssets')->willReturn(new ArrayIterator($assets));

		$assetDataProvider = self::createStub(AssetDataProviderInterface::class);
		$assetDataProvider->method('getAssetData')->willReturnCallback(
			function (User $u, Portfolio $p, Asset $a): AssetDataDto {
				$values = [
					1 => ['units' => new Decimal('1'), 'value' => new Decimal('600')],
					2 => ['units' => new Decimal('1'), 'value' => new Decimal('200')],
					3 => ['units' => new Decimal('1'), 'value' => new Decimal('200')],
					4 => ['units' => new Decimal('0'), 'value' => new Decimal('0')],
				];
				$entry = $values[$a->id] ?? ['units' => new Decimal('0'), 'value' => new Decimal('0')];
				return $this->makeAssetDataDto(units: $entry['units'], value: $entry['value']);
			},
		);

		$groupDataProvider = self::createStub(GroupDataProviderInterface::class);
		$groupDataProvider->method('getGroupData')->willReturnCallback(
			fn (Group $g): CalculatedDataDto => $this->makeCalculatedDataDto($g->id === 1 ? new Decimal('800') : new Decimal('200')),
		);

		$portfolioDataProvider = self::createStub(PortfolioDataProviderInterface::class);
		$portfolioDataProvider->method('getPortfolioData')->willReturn($this->makeCalculatedDataDto(new Decimal('1000')));

		$provider = new GroupWithGroupDataProvider(
			portfolioDataProvider: $portfolioDataProvider,
			assetProvider: $assetProvider,
			groupDataProvider: $groupDataProvider,
			assetDataProvider: $assetDataProvider,
		);

		$result = $provider->getGroupsWithGroupData(UserFixture::getUser(), PortfolioFixture::getPortfolio(), new DateTimeImmutable());

		self::assertCount(2, $result);

		// Order follows the asset iteration: group 1 first (asset 1), then group 2 (asset 2)
		self::assertSame('Tech', $result[0]->name);
		self::assertSame(80.0, $result[0]->percentage);
		self::assertSame([1, 3], $result[0]->assetIds);

		self::assertSame('Bonds', $result[1]->name);
		self::assertSame(20.0, $result[1]->percentage);
		self::assertSame([2], $result[1]->assetIds);
	}

	private function makeAssetWithGroup(int $id, string $tickerSymbol, Group $group): Asset
	{
		$ticker = TickerFixture::getTicker(id: $id, ticker: $tickerSymbol);
		return AssetFixture::getAsset(id: $id, ticker: $ticker, group: $group);
	}

	private function makeCalculatedDataDto(Decimal $value): CalculatedDataDto
	{
		$zero = new Decimal('0');
		return new CalculatedDataDto(
			date: new DateTimeImmutable(),
			value: $value,
			transactionValue: $zero,
			gain: $zero,
			gainPercentage: 0.0,
			gainPercentagePerAnnum: 0.0,
			realizedGain: $zero,
			dividendYield: $zero,
			dividendYieldPercentage: 0.0,
			dividendYieldPercentagePerAnnum: 0.0,
			fxImpact: $zero,
			fxImpactPercentage: 0.0,
			fxImpactPercentagePerAnnum: 0.0,
			return: $zero,
			returnPercentage: 0.0,
			returnPercentagePerAnnum: 0.0,
			tax: $zero,
			fee: $zero,
		);
	}

	private function makeAssetDataDto(Decimal $units, Decimal $value): AssetDataDto
	{
		$zero = new Decimal('0');
		return new AssetDataDto(
			date: new DateTimeImmutable(),
			price: $zero,
			units: $units,
			value: $value,
			transactionValue: $zero,
			transactionValueDefaultCurrency: $zero,
			averagePrice: $zero,
			averagePriceDefaultCurrency: $zero,
			gain: $zero,
			gainDefaultCurrency: $zero,
			realizedGain: $zero,
			realizedGainDefaultCurrency: $zero,
			gainPercentage: 0.0,
			gainPercentagePerAnnum: 0.0,
			dividendYield: $zero,
			dividendYieldDefaultCurrency: $zero,
			dividendYieldPercentage: 0.0,
			dividendYieldPercentagePerAnnum: 0.0,
			fxImpact: $zero,
			fxImpactPercentage: 0.0,
			fxImpactPercentagePerAnnum: 0.0,
			return: $zero,
			returnPercentage: 0.0,
			returnPercentagePerAnnum: 0.0,
			tax: $zero,
			taxDefaultCurrency: $zero,
			fee: $zero,
			feeDefaultCurrency: $zero,
			firstTransactionActionCreated: new DateTimeImmutable(),
		);
	}
}
