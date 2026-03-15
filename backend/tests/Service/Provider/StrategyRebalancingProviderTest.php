<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Provider;

use ArrayIterator;
use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Strategy;
use FinGather\Model\Entity\StrategyItem;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\AssetDataDto;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use FinGather\Service\Provider\AssetDataProvider;
use FinGather\Service\Provider\AssetProvider;
use FinGather\Service\Provider\CurrencyProvider;
use FinGather\Service\Provider\ExchangeRateProvider;
use FinGather\Service\Provider\GroupDataProvider;
use FinGather\Service\Provider\GroupProvider;
use FinGather\Service\Provider\PortfolioDataProvider;
use FinGather\Service\Provider\StrategyRebalancingProvider;
use FinGather\Tests\Fixtures\Model\Entity\AssetFixture;
use FinGather\Tests\Fixtures\Model\Entity\CurrencyFixture;
use FinGather\Tests\Fixtures\Model\Entity\GroupFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StrategyRebalancingProvider::class)]
final class StrategyRebalancingProviderTest extends TestCase
{
	private User $user;

	private Portfolio $portfolio;

	private DateTimeImmutable $dateTime;

	protected function setUp(): void
	{
		$this->user = UserFixture::getUser();
		$this->portfolio = PortfolioFixture::getPortfolio();
		$this->dateTime = new DateTimeImmutable();
	}

	public function testAssetItemBuyOnlyWhenUnderweight(): void
	{
		// Portfolio value: 1000, target: 60% → target value 600, current: 400 → buy 200
		$asset = AssetFixture::getAsset(id: 1);
		$strategy = $this->makeStrategy([$this->makeAssetItem($asset, '60')]);

		$provider = $this->makeProvider(
			portfolioValue: new Decimal('1000'),
			assets: [$asset],
			assetDataValue: new Decimal('400'),
			assetDataPrice: new Decimal('50'),
		);

		$result = $provider->getStrategyRebalancing(
			user: $this->user,
			portfolio: $this->portfolio,
			strategy: $strategy,
			dateTime: $this->dateTime,
			cashToInvest: new Decimal('0'),
			cashCurrencyId: null,
			allowSelling: false,
		);

		// item + Others
		self::assertCount(2, $result->items);
		$item = $result->items[0];
		self::assertSame(60.0, $item->targetPercentage);
		self::assertSame(40.0, $item->actualPercentage);
		self::assertSame(-20.0, $item->differencePercentage);
		self::assertEquals(new Decimal('200'), $item->suggestedTradeValue);
		// 200 / 50
		self::assertEquals(new Decimal('4'), $item->suggestedTradeUnits);
	}

	public function testAssetItemNoSellWhenBuyOnlyAndOverweight(): void
	{
		// Portfolio value: 1000, target: 40% → target value 400, current: 600 → raw -200, clamped to 0
		$asset = AssetFixture::getAsset(id: 1);
		$strategy = $this->makeStrategy([$this->makeAssetItem($asset, '40')]);

		$provider = $this->makeProvider(
			portfolioValue: new Decimal('1000'),
			assets: [$asset],
			assetDataValue: new Decimal('600'),
			assetDataPrice: new Decimal('50'),
		);

		$result = $provider->getStrategyRebalancing(
			user: $this->user,
			portfolio: $this->portfolio,
			strategy: $strategy,
			dateTime: $this->dateTime,
			cashToInvest: new Decimal('0'),
			cashCurrencyId: null,
			allowSelling: false,
		);

		$item = $result->items[0];
		self::assertEquals(new Decimal('0'), $item->suggestedTradeValue);
		self::assertEquals(new Decimal('0'), $item->suggestedTradeUnits);
	}

	public function testAssetItemSellWhenAllowSellingAndOverweight(): void
	{
		// Portfolio value: 1000, target: 40%, current: 600 → sell 200
		$asset = AssetFixture::getAsset(id: 1);
		$strategy = $this->makeStrategy([$this->makeAssetItem($asset, '40')]);

		$provider = $this->makeProvider(
			portfolioValue: new Decimal('1000'),
			assets: [$asset],
			assetDataValue: new Decimal('600'),
			assetDataPrice: new Decimal('50'),
		);

		$result = $provider->getStrategyRebalancing(
			user: $this->user,
			portfolio: $this->portfolio,
			strategy: $strategy,
			dateTime: $this->dateTime,
			cashToInvest: new Decimal('0'),
			cashCurrencyId: null,
			allowSelling: true,
		);

		$item = $result->items[0];
		self::assertEquals(new Decimal('-200'), $item->suggestedTradeValue);
		// -200 / 50
		self::assertEquals(new Decimal('-4'), $item->suggestedTradeUnits);
	}

	public function testCashToInvestIncreasesTotalPortfolioValue(): void
	{
		// Portfolio: 1000, cash: 500, total: 1500, target 100% → target 1500, current 1000 → buy 500
		$asset = AssetFixture::getAsset(id: 1);
		$strategy = $this->makeStrategy([$this->makeAssetItem($asset, '100')]);

		$provider = $this->makeProvider(
			portfolioValue: new Decimal('1000'),
			assets: [$asset],
			assetDataValue: new Decimal('1000'),
			assetDataPrice: new Decimal('100'),
		);

		$result = $provider->getStrategyRebalancing(
			user: $this->user,
			portfolio: $this->portfolio,
			strategy: $strategy,
			dateTime: $this->dateTime,
			cashToInvest: new Decimal('500'),
			cashCurrencyId: null,
			allowSelling: false,
		);

		self::assertEquals(new Decimal('500'), $result->cashToInvest);
		self::assertEquals(new Decimal('500'), $result->items[0]->suggestedTradeValue);
	}

	public function testGroupItemHasNoUnitsOrPrice(): void
	{
		// Group item: no trade units or price should be returned
		$group = GroupFixture::getGroup(isOthers: false);
		$group->id = 1;
		$strategy = $this->makeStrategy([$this->makeGroupItem($group, '50')]);

		$provider = $this->makeProviderWithGroup(
			portfolioValue: new Decimal('1000'),
			group: $group,
			groupDataValue: new Decimal('400'),
		);

		$result = $provider->getStrategyRebalancing(
			user: $this->user,
			portfolio: $this->portfolio,
			strategy: $strategy,
			dateTime: $this->dateTime,
			cashToInvest: new Decimal('0'),
			cashCurrencyId: null,
			allowSelling: false,
		);

		$item = $result->items[0];
		self::assertSame(50.0, $item->targetPercentage);
		// 500 - 400
		self::assertEquals(new Decimal('100'), $item->suggestedTradeValue);
		self::assertNull($item->suggestedTradeUnits);
		self::assertNull($item->currentPrice);
		self::assertNotNull($item->groupId);
		self::assertNull($item->assetId);
	}

	public function testOthersRowAppearsWhenTargetSumIsBelow100(): void
	{
		// Strategy only covers 60%, so Others should appear for remaining 40%
		$asset = AssetFixture::getAsset(id: 1);
		$strategy = $this->makeStrategy([$this->makeAssetItem($asset, '60')]);

		$provider = $this->makeProvider(
			portfolioValue: new Decimal('1000'),
			assets: [$asset],
			assetDataValue: new Decimal('600'),
			assetDataPrice: new Decimal('50'),
		);

		$result = $provider->getStrategyRebalancing(
			user: $this->user,
			portfolio: $this->portfolio,
			strategy: $strategy,
			dateTime: $this->dateTime,
			cashToInvest: new Decimal('0'),
			cashCurrencyId: null,
			allowSelling: false,
		);

		$othersItem = $result->items[1];
		self::assertTrue($othersItem->isOthers);
		self::assertSame(40.0, $othersItem->targetPercentage);
		self::assertEquals(new Decimal('0'), $othersItem->suggestedTradeValue);
		self::assertNull($othersItem->suggestedTradeUnits);
	}

	public function testCurrencyConversionAppliedWhenCashCurrencyDiffers(): void
	{
		// Cash: 100 EUR, rate EUR→USD: 1.1 → cash in portfolio currency = 110
		$asset = AssetFixture::getAsset(id: 1);
		$strategy = $this->makeStrategy([$this->makeAssetItem($asset, '100')]);

		$eurCurrency = CurrencyFixture::getCurrency(id: 2, code: 'EUR');
		$usdCurrency = CurrencyFixture::getCurrency(id: 1, code: 'USD');
		$portfolio = PortfolioFixture::getPortfolio(currency: $usdCurrency);

		$currencyProvider = self::createStub(CurrencyProvider::class);
		$currencyProvider->method('getCurrency')->willReturn($eurCurrency);

		$exchangeRateProvider = self::createStub(ExchangeRateProvider::class);
		$exchangeRateProvider->method('getExchangeRate')->willReturn(new Decimal('1.1'));

		$provider = $this->makeProvider(
			portfolioValue: new Decimal('1000'),
			assets: [$asset],
			assetDataValue: new Decimal('1000'),
			assetDataPrice: new Decimal('100'),
			currencyProvider: $currencyProvider,
			exchangeRateProvider: $exchangeRateProvider,
		);

		$result = $provider->getStrategyRebalancing(
			user: $this->user,
			portfolio: $portfolio,
			strategy: $strategy,
			dateTime: $this->dateTime,
			cashToInvest: new Decimal('100'),
			cashCurrencyId: 2,
			allowSelling: false,
		);

		// Converted cash = 110, so target = 1110 and trade = 110
		self::assertEquals(new Decimal('110'), $result->items[0]->suggestedTradeValue);
	}

	// --- helpers ---

	/** @param list<StrategyItem> $strategyItems */
	private function makeStrategy(array $strategyItems): Strategy
	{
		$strategy = new Strategy(
			user: $this->user,
			portfolio: $this->portfolio,
			name: 'Test Strategy',
			isDefault: false,
			strategyItems: new ArrayIterator($strategyItems),
		);
		$strategy->id = 1;
		return $strategy;
	}

	private function makeAssetItem(Asset $asset, string $percentage): StrategyItem
	{
		$strategy = new Strategy(
			user: $this->user,
			portfolio: $this->portfolio,
			name: 'Test Strategy',
			isDefault: false,
			strategyItems: new ArrayIterator([]),
		);
		$strategy->id = 1;

		$item = new StrategyItem(
			strategy: $strategy,
			asset: $asset,
			group: null,
			percentage: new Decimal($percentage),
		);
		$item->id = 1;
		return $item;
	}

	private function makeGroupItem(Group $group, string $percentage): StrategyItem
	{
		$strategy = new Strategy(
			user: $this->user,
			portfolio: $this->portfolio,
			name: 'Test Strategy',
			isDefault: false,
			strategyItems: new ArrayIterator([]),
		);
		$strategy->id = 1;

		$item = new StrategyItem(
			strategy: $strategy,
			asset: null,
			group: $group,
			percentage: new Decimal($percentage),
		);
		$item->id = 1;
		return $item;
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

	private function makeAssetDataDto(Decimal $value, Decimal $price): AssetDataDto
	{
		$zero = new Decimal('0');
		return new AssetDataDto(
			date: new DateTimeImmutable(),
			price: $price,
			units: new Decimal('1'),
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

	/** @param Asset[] $assets */
	private function makeProvider(
		Decimal $portfolioValue,
		array $assets,
		Decimal $assetDataValue,
		Decimal $assetDataPrice,
		?CurrencyProvider $currencyProvider = null,
		?ExchangeRateProvider $exchangeRateProvider = null,
	): StrategyRebalancingProvider {
		$portfolioDataProvider = self::createStub(PortfolioDataProvider::class);
		$portfolioDataProvider->method('getPortfolioData')
			->willReturn($this->makeCalculatedDataDto($portfolioValue));

		$assetProvider = self::createStub(AssetProvider::class);
		$assetProvider->method('getAssets')->willReturn(new ArrayIterator($assets));

		$assetDataProvider = self::createStub(AssetDataProvider::class);
		$assetDataProvider->method('getAssetData')
			->willReturn($this->makeAssetDataDto($assetDataValue, $assetDataPrice));

		$groupDataProvider = self::createStub(GroupDataProvider::class);
		$groupDataProvider->method('getGroupData')
			->willReturn($this->makeCalculatedDataDto(new Decimal('0')));

		$groupProvider = self::createStub(GroupProvider::class);
		$groupProvider->method('getGroups')->willReturn(new ArrayIterator([]));

		return new StrategyRebalancingProvider(
			portfolioDataProvider: $portfolioDataProvider,
			assetProvider: $assetProvider,
			assetDataProvider: $assetDataProvider,
			groupDataProvider: $groupDataProvider,
			groupProvider: $groupProvider,
			currencyProvider: $currencyProvider ?? self::createStub(CurrencyProvider::class),
			exchangeRateProvider: $exchangeRateProvider ?? self::createStub(ExchangeRateProvider::class),
		);
	}

	private function makeProviderWithGroup(Decimal $portfolioValue, Group $group, Decimal $groupDataValue,): StrategyRebalancingProvider
	{
		$portfolioDataProvider = self::createStub(PortfolioDataProvider::class);
		$portfolioDataProvider->method('getPortfolioData')
			->willReturn($this->makeCalculatedDataDto($portfolioValue));

		$assetProvider = self::createStub(AssetProvider::class);
		$assetProvider->method('getAssets')->willReturn(new ArrayIterator([]));

		$assetDataProvider = self::createStub(AssetDataProvider::class);

		$groupDataProvider = self::createStub(GroupDataProvider::class);
		$groupDataProvider->method('getGroupData')
			->willReturn($this->makeCalculatedDataDto($groupDataValue));

		$groupProvider = self::createStub(GroupProvider::class);
		$groupProvider->method('getGroups')->willReturn(new ArrayIterator([$group]));

		return new StrategyRebalancingProvider(
			portfolioDataProvider: $portfolioDataProvider,
			assetProvider: $assetProvider,
			assetDataProvider: $assetDataProvider,
			groupDataProvider: $groupDataProvider,
			groupProvider: $groupProvider,
			currencyProvider: self::createStub(CurrencyProvider::class),
			exchangeRateProvider: self::createStub(ExchangeRateProvider::class),
		);
	}
}
