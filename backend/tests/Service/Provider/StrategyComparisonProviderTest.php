<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Provider;

use ArrayIterator;
use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Dto\StrategyComparisonItemDto;
use FinGather\Dto\StrategyWithComparisonDto;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Country;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Industry;
use FinGather\Model\Entity\Market;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Sector;
use FinGather\Model\Entity\Strategy;
use FinGather\Model\Entity\StrategyItem;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\AssetDataDto;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use FinGather\Service\Provider\AssetDataProviderInterface;
use FinGather\Service\Provider\AssetProviderInterface;
use FinGather\Service\Provider\GroupDataProviderInterface;
use FinGather\Service\Provider\GroupProviderInterface;
use FinGather\Service\Provider\PortfolioDataProviderInterface;
use FinGather\Service\Provider\StrategyComparisonProvider;
use FinGather\Tests\Fixtures\Model\Entity\AssetFixture;
use FinGather\Tests\Fixtures\Model\Entity\GroupFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use FinGather\Utils\CalculatorUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StrategyComparisonProvider::class)]
#[UsesClass(StrategyComparisonItemDto::class)]
#[UsesClass(StrategyWithComparisonDto::class)]
#[UsesClass(Asset::class)]
#[UsesClass(Country::class)]
#[UsesClass(Currency::class)]
#[UsesClass(Group::class)]
#[UsesClass(Industry::class)]
#[UsesClass(Market::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(Sector::class)]
#[UsesClass(Strategy::class)]
#[UsesClass(StrategyItem::class)]
#[UsesClass(Ticker::class)]
#[UsesClass(User::class)]
#[UsesClass(AssetDataDto::class)]
#[UsesClass(CalculatedDataDto::class)]
#[UsesClass(CalculatorUtils::class)]
final class StrategyComparisonProviderTest extends TestCase
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

	public function testAssetItemReportsActualVsTargetDifference(): void
	{
		// Asset value 400 of portfolio 1000 → actual 40%, target 60% → diff -20%
		$asset = AssetFixture::getAsset(id: 1);
		$strategy = $this->makeStrategy([$this->makeAssetItem($asset, '60')]);

		$provider = $this->makeProvider(
			portfolioValue: new Decimal('1000'),
			assets: [$asset],
			assetDataValueMap: [1 => new Decimal('400')],
		);

		$result = $provider->getStrategyWithComparison($this->user, $this->portfolio, $strategy, $this->dateTime);

		self::assertSame('Test Strategy', $result->name);
		// Asset item plus Others row (60% target / 40% actual leaves 40% / 60% unaccounted).
		self::assertCount(2, $result->comparisonItems);

		$item = $result->comparisonItems[0];
		self::assertSame('Apple Inc.', $item->name);
		self::assertSame(1, $item->assetId);
		self::assertNull($item->groupId);
		self::assertFalse($item->isOthers);
		self::assertSame(60.0, $item->targetPercentage);
		self::assertSame(40.0, $item->actualPercentage);
		self::assertSame(-20.0, $item->differencePercentage);
	}

	public function testClosedAssetContributesZeroActualPercentage(): void
	{
		// Closed assets should be skipped → actual percentage falls back to 0
		$asset = AssetFixture::getAsset(id: 1);
		$strategy = $this->makeStrategy([$this->makeAssetItem($asset, '50')]);

		$provider = $this->makeProvider(
			portfolioValue: new Decimal('1000'),
			assets: [$asset],
			assetDataValueMap: [1 => new Decimal('400')],
			closedAssetIds: [1],
		);

		$result = $provider->getStrategyWithComparison($this->user, $this->portfolio, $strategy, $this->dateTime);

		// Asset item plus Others row (50% target / 100% actual unaccounted)
		self::assertCount(2, $result->comparisonItems);

		$item = $result->comparisonItems[0];
		self::assertSame(50.0, $item->targetPercentage);
		self::assertSame(0.0, $item->actualPercentage);
		self::assertSame(-50.0, $item->differencePercentage);
	}

	public function testGroupItemPullsActualFromGroupData(): void
	{
		// Group value 250 of portfolio 1000 → actual 25%, target 30% → diff -5%
		$group = GroupFixture::getGroup(name: 'Tech', color: '#abcdef', isOthers: false);
		$group->id = 7;
		$strategy = $this->makeStrategy([$this->makeGroupItem($group, '30')]);

		$provider = $this->makeProvider(
			portfolioValue: new Decimal('1000'),
			assets: [],
			assetDataValueMap: [],
			groups: [$group],
			groupDataValueMap: [7 => new Decimal('250')],
		);

		$result = $provider->getStrategyWithComparison($this->user, $this->portfolio, $strategy, $this->dateTime);

		self::assertCount(2, $result->comparisonItems);

		$item = $result->comparisonItems[0];
		self::assertSame('Tech', $item->name);
		self::assertSame('#abcdef', $item->color);
		self::assertNull($item->assetId);
		self::assertSame(7, $item->groupId);
		self::assertFalse($item->isOthers);
		self::assertSame(30.0, $item->targetPercentage);
		self::assertSame(25.0, $item->actualPercentage);
		self::assertSame(-5.0, $item->differencePercentage);
	}

	public function testOthersRowAddedWhenTargetSumBelow100(): void
	{
		// Strategy covers 60%; Others fills the remaining 40%.
		$asset = AssetFixture::getAsset(id: 1);
		$strategy = $this->makeStrategy([$this->makeAssetItem($asset, '60')]);

		$provider = $this->makeProvider(
			portfolioValue: new Decimal('1000'),
			assets: [$asset],
			assetDataValueMap: [1 => new Decimal('600')],
		);

		$result = $provider->getStrategyWithComparison($this->user, $this->portfolio, $strategy, $this->dateTime);

		self::assertCount(2, $result->comparisonItems);

		$others = $result->comparisonItems[1];
		self::assertSame('Others', $others->name);
		self::assertNull($others->color);
		self::assertNull($others->assetId);
		self::assertNull($others->groupId);
		self::assertTrue($others->isOthers);
		self::assertSame(40.0, $others->targetPercentage);
		self::assertSame(40.0, $others->actualPercentage);
		self::assertSame(0.0, $others->differencePercentage);
	}

	public function testOthersRowOmittedWhenTargetAndActualBothCover100(): void
	{
		// Asset perfectly matches strategy at 100%; no Others row needed.
		$asset = AssetFixture::getAsset(id: 1);
		$strategy = $this->makeStrategy([$this->makeAssetItem($asset, '100')]);

		$provider = $this->makeProvider(
			portfolioValue: new Decimal('1000'),
			assets: [$asset],
			assetDataValueMap: [1 => new Decimal('1000')],
		);

		$result = $provider->getStrategyWithComparison($this->user, $this->portfolio, $strategy, $this->dateTime);

		self::assertCount(1, $result->comparisonItems);
		self::assertFalse($result->comparisonItems[0]->isOthers);
	}

	public function testStrategyMetadataPropagated(): void
	{
		$asset = AssetFixture::getAsset(id: 1);
		$strategy = $this->makeStrategy(
			[$this->makeAssetItem($asset, '100')],
			id: 42,
			name: 'Aggressive',
			isDefault: true,
		);

		$provider = $this->makeProvider(
			portfolioValue: new Decimal('1000'),
			assets: [$asset],
			assetDataValueMap: [1 => new Decimal('1000')],
		);

		$result = $provider->getStrategyWithComparison($this->user, $this->portfolio, $strategy, $this->dateTime);

		self::assertSame(42, $result->id);
		self::assertSame('Aggressive', $result->name);
		self::assertTrue($result->isDefault);
	}

	// --- helpers ---

	/** @param list<StrategyItem> $strategyItems */
	private function makeStrategy(array $strategyItems, int $id = 1, string $name = 'Test Strategy', bool $isDefault = false,): Strategy
	{
		$strategy = new Strategy(
			user: $this->user,
			portfolio: $this->portfolio,
			name: $name,
			isDefault: $isDefault,
			strategyItems: new ArrayIterator($strategyItems),
		);
		$strategy->id = $id;
		return $strategy;
	}

	private function makeAssetItem(Asset $asset, string $percentage): StrategyItem
	{
		$strategy = new Strategy(
			user: $this->user,
			portfolio: $this->portfolio,
			name: 'parent',
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
			name: 'parent',
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

	private function makeAssetDataDto(Decimal $value, bool $closed): AssetDataDto
	{
		$zero = new Decimal('0');
		return new AssetDataDto(
			date: new DateTimeImmutable(),
			price: $zero,
			units: $closed ? $zero : new Decimal('1'),
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

	/**
	 * @param list<Asset> $assets
	 * @param array<int, Decimal> $assetDataValueMap Asset id → value (closed assets in $closedAssetIds skip the percentage but still return data).
	 * @param list<int> $closedAssetIds
	 * @param list<Group> $groups Non-Others groups.
	 * @param array<int, Decimal> $groupDataValueMap Group id → value. Missing entries default to zero.
	 */
	private function makeProvider(
		Decimal $portfolioValue,
		array $assets,
		array $assetDataValueMap,
		array $closedAssetIds = [],
		array $groups = [],
		array $groupDataValueMap = [],
	): StrategyComparisonProvider {
		$portfolioDataProvider = self::createStub(PortfolioDataProviderInterface::class);
		$portfolioDataProvider->method('getPortfolioData')
			->willReturn($this->makeCalculatedDataDto($portfolioValue));

		$assetProvider = self::createStub(AssetProviderInterface::class);
		$assetProvider->method('getAssets')->willReturn(new ArrayIterator($assets));

		$assetDataProvider = self::createStub(AssetDataProviderInterface::class);
		$assetDataProvider->method('getAssetData')->willReturnCallback(
			function (User $user, Portfolio $portfolio, Asset $asset) use ($assetDataValueMap, $closedAssetIds): ?AssetDataDto {
				$value = $assetDataValueMap[$asset->id] ?? null;
				if ($value === null) {
					return null;
				}
				return $this->makeAssetDataDto($value, in_array($asset->id, $closedAssetIds, true));
			},
		);

		$othersGroup = GroupFixture::getGroup(name: 'Others', color: '#000000', isOthers: true);
		$othersGroup->id = 999;

		$groupProvider = self::createStub(GroupProviderInterface::class);
		$groupProvider->method('getGroups')->willReturn(new ArrayIterator($groups));
		$groupProvider->method('getOthersGroup')->willReturn($othersGroup);

		$groupDataProvider = self::createStub(GroupDataProviderInterface::class);
		$groupDataProvider->method('getGroupData')->willReturnCallback(
			function (Group $group) use ($groupDataValueMap): CalculatedDataDto {
				return $this->makeCalculatedDataDto($groupDataValueMap[$group->id] ?? new Decimal('0'));
			},
		);

		return new StrategyComparisonProvider(
			portfolioDataProvider: $portfolioDataProvider,
			assetProvider: $assetProvider,
			assetDataProvider: $assetDataProvider,
			groupDataProvider: $groupDataProvider,
			groupProvider: $groupProvider,
		);
	}
}
