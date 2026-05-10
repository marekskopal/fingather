<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\DataCalculator;

use ArrayIterator;
use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Country;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\TaxJurisdictionEnum;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Industry;
use FinGather\Model\Entity\Market;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Sector;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\AssetDataDto;
use FinGather\Service\DataCalculator\Dto\TaxOptimizationDto;
use FinGather\Service\DataCalculator\Dto\TaxOptimizationRationaleEnum;
use FinGather\Service\DataCalculator\Dto\TaxOptimizationSuggestionDto;
use FinGather\Service\DataCalculator\TaxOptimizationCalculator;
use FinGather\Service\Provider\AssetDataProviderInterface;
use FinGather\Service\Provider\AssetProviderInterface;
use FinGather\Service\Provider\CurrentTransactionProviderInterface;
use FinGather\Service\Tax\Jurisdiction\CzechRepublicTaxJurisdictionRules;
use FinGather\Service\Tax\Jurisdiction\GenericTaxJurisdictionRules;
use FinGather\Service\Tax\Jurisdiction\GermanyTaxJurisdictionRules;
use FinGather\Service\Tax\Jurisdiction\SlovakiaTaxJurisdictionRules;
use FinGather\Service\Tax\Jurisdiction\TaxJurisdictionRulesFactory;
use FinGather\Tests\Fixtures\Model\Entity\AssetFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\TransactionFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaxOptimizationCalculator::class)]
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
#[UsesClass(AssetDataDto::class)]
#[UsesClass(TaxOptimizationDto::class)]
#[UsesClass(TaxOptimizationSuggestionDto::class)]
#[UsesClass(CzechRepublicTaxJurisdictionRules::class)]
#[UsesClass(SlovakiaTaxJurisdictionRules::class)]
#[UsesClass(GermanyTaxJurisdictionRules::class)]
#[UsesClass(GenericTaxJurisdictionRules::class)]
#[UsesClass(TaxJurisdictionRulesFactory::class)]
final class TaxOptimizationCalculatorTest extends TestCase
{
	private DateTimeImmutable $asOf;

	protected function setUp(): void
	{
		$this->asOf = new DateTimeImmutable('2026-05-09');
	}

	public function testCzechHarvestNowForShortTermLoss(): void
	{
		$asset = AssetFixture::getAsset();
		$assetData = $this->makeAssetData(firstBuyDaysAgo: 100, gainDefault: '-500', value: '4500', costBasis: '5000');

		$result = $this->runOptimization(jurisdiction: TaxJurisdictionEnum::CzechRepublic, assetsAndData: [[$asset, $assetData]]);

		self::assertCount(1, $result->harvestNow);
		self::assertSame(TaxOptimizationRationaleEnum::HarvestBeforeLongTerm, $result->harvestNow[0]->rationale);
		self::assertSame(995, $result->harvestNow[0]->daysUntilLongTerm);
		// 500 loss * 0.15 = 75 estimated saving
		self::assertSame(75.0, $result->estimatedTaxSavedByHarvestingNow->toFloat());
	}

	public function testCzechHoldForTaxFreeGainNearThreshold(): void
	{
		$asset = AssetFixture::getAsset();
		// 1000 days held → 95 days until long-term
		$assetData = $this->makeAssetData(firstBuyDaysAgo: 1000, gainDefault: '2000', value: '7000', costBasis: '5000');

		$result = $this->runOptimization(jurisdiction: TaxJurisdictionEnum::CzechRepublic, assetsAndData: [[$asset, $assetData]]);

		self::assertCount(1, $result->holdForTaxFreeGain);
		self::assertSame(95, $result->holdForTaxFreeGain[0]->daysUntilLongTerm);
		self::assertSame(TaxOptimizationRationaleEnum::HoldForTaxFreeGain, $result->holdForTaxFreeGain[0]->rationale);
		// 2000 gain * 0.15 = 300 estimated saving by waiting
		self::assertSame(300.0, $result->estimatedTaxSavedByWaiting->toFloat());
	}

	public function testCzechAlreadyTaxFree(): void
	{
		$asset = AssetFixture::getAsset();
		$assetData = $this->makeAssetData(firstBuyDaysAgo: 1500, gainDefault: '3000', value: '8000', costBasis: '5000');

		$result = $this->runOptimization(jurisdiction: TaxJurisdictionEnum::CzechRepublic, assetsAndData: [[$asset, $assetData]]);

		self::assertCount(1, $result->alreadyTaxFree);
		self::assertSame(0, $result->alreadyTaxFree[0]->daysUntilLongTerm);
		self::assertCount(0, $result->harvestNow);
	}

	public function testCzechLossNoLongerDeductible(): void
	{
		$asset = AssetFixture::getAsset();
		$assetData = $this->makeAssetData(firstBuyDaysAgo: 1500, gainDefault: '-300', value: '4700', costBasis: '5000');

		$result = $this->runOptimization(jurisdiction: TaxJurisdictionEnum::CzechRepublic, assetsAndData: [[$asset, $assetData]]);

		self::assertCount(1, $result->lossNoLongerDeductible);
		self::assertSame(TaxOptimizationRationaleEnum::LossNoLongerDeductible, $result->lossNoLongerDeductible[0]->rationale);
		self::assertCount(0, $result->harvestNow);
		// Tax saving estimate must NOT include this — loss isn't deductible
		self::assertSame(0.0, $result->estimatedTaxSavedByHarvestingNow->toFloat());
	}

	public function testCzechWinningShortTermFarFromThreshold(): void
	{
		$asset = AssetFixture::getAsset();
		// 100 days held → 995 days until long-term, far above 365 threshold
		$assetData = $this->makeAssetData(firstBuyDaysAgo: 100, gainDefault: '500', value: '5500', costBasis: '5000');

		$result = $this->runOptimization(jurisdiction: TaxJurisdictionEnum::CzechRepublic, assetsAndData: [[$asset, $assetData]]);

		self::assertCount(1, $result->winningShortTerm);
		self::assertCount(0, $result->holdForTaxFreeGain);
	}

	public function testCzechHarvestNowSortedByUrgency(): void
	{
		$urgentAsset = AssetFixture::getAsset(id: 1);
		$lessUrgentAsset = AssetFixture::getAsset(id: 2);

		// 1090 days held (5 to long-term) is more urgent than 100 days (995 to long-term)
		$urgent = $this->makeAssetData(firstBuyDaysAgo: 1090, gainDefault: '-100', value: '4900', costBasis: '5000');
		$lessUrgent = $this->makeAssetData(firstBuyDaysAgo: 100, gainDefault: '-200', value: '4800', costBasis: '5000');

		$result = $this->runOptimization(
			jurisdiction: TaxJurisdictionEnum::CzechRepublic,
			assetsAndData: [[$lessUrgentAsset, $lessUrgent], [$urgentAsset, $urgent]],
		);

		self::assertCount(2, $result->harvestNow);
		// Most urgent first
		self::assertSame(5, $result->harvestNow[0]->daysUntilLongTerm);
		self::assertSame(995, $result->harvestNow[1]->daysUntilLongTerm);
	}

	public function testGenericHarvestsAnyLossRegardlessOfHoldingPeriod(): void
	{
		$shortAsset = AssetFixture::getAsset(id: 1);
		$longAsset = AssetFixture::getAsset(id: 2);

		$short = $this->makeAssetData(firstBuyDaysAgo: 50, gainDefault: '-100', value: '4900', costBasis: '5000');
		$long = $this->makeAssetData(firstBuyDaysAgo: 2000, gainDefault: '-200', value: '4800', costBasis: '5000');

		$result = $this->runOptimization(
			jurisdiction: TaxJurisdictionEnum::Generic,
			assetsAndData: [[$shortAsset, $short], [$longAsset, $long]],
		);

		self::assertCount(2, $result->harvestNow);
		self::assertCount(0, $result->lossNoLongerDeductible);
		// Generic has no long-term concept → no rate → null tax impact
		self::assertSame(0.0, $result->estimatedTaxSavedByHarvestingNow->toFloat());
		self::assertSame(TaxJurisdictionEnum::Generic, $result->jurisdiction);
		self::assertNull($result->longTermHoldingDays);
	}

	public function testGenericWinningPositionsBucketAsShortTerm(): void
	{
		$asset = AssetFixture::getAsset();
		$assetData = $this->makeAssetData(firstBuyDaysAgo: 50, gainDefault: '500', value: '5500', costBasis: '5000');

		$result = $this->runOptimization(jurisdiction: TaxJurisdictionEnum::Generic, assetsAndData: [[$asset, $assetData]]);

		self::assertCount(1, $result->winningShortTerm);
		self::assertCount(0, $result->alreadyTaxFree);
	}

	public function testClosedPositionsSkipped(): void
	{
		$asset = AssetFixture::getAsset();
		$assetData = $this->makeAssetData(firstBuyDaysAgo: 100, gainDefault: '0', value: '0', costBasis: '0', units: '0');

		$result = $this->runOptimization(jurisdiction: TaxJurisdictionEnum::CzechRepublic, assetsAndData: [[$asset, $assetData]]);

		self::assertCount(0, $result->harvestNow);
		self::assertCount(0, $result->holdForTaxFreeGain);
		self::assertCount(0, $result->lossNoLongerDeductible);
		self::assertCount(0, $result->alreadyTaxFree);
		self::assertCount(0, $result->winningShortTerm);
	}

	public function testHoldingVariesByBrokerFlagSetWhenAssetHasMultipleBrokers(): void
	{
		$asset = AssetFixture::getAsset(id: 7);
		$assetData = $this->makeAssetData(firstBuyDaysAgo: 100, gainDefault: '-50', value: '950', costBasis: '1000');

		$transactionsByAsset = [
			7 => [
				TransactionFixture::getTransaction(id: 1, asset: $asset, brokerId: 1),
				TransactionFixture::getTransaction(id: 2, asset: $asset, brokerId: 2),
			],
		];

		$result = $this->runOptimization(
			jurisdiction: TaxJurisdictionEnum::CzechRepublic,
			assetsAndData: [[$asset, $assetData]],
			transactionsByAsset: $transactionsByAsset,
		);

		self::assertCount(1, $result->harvestNow);
		self::assertTrue($result->harvestNow[0]->holdingVariesByBroker);
	}

	public function testHoldingVariesByBrokerFlagFalseForSingleBroker(): void
	{
		$asset = AssetFixture::getAsset(id: 8);
		$assetData = $this->makeAssetData(firstBuyDaysAgo: 100, gainDefault: '-50', value: '950', costBasis: '1000');

		$transactionsByAsset = [
			8 => [
				TransactionFixture::getTransaction(id: 1, asset: $asset, brokerId: 1),
				TransactionFixture::getTransaction(id: 2, asset: $asset, brokerId: 1),
			],
		];

		$result = $this->runOptimization(
			jurisdiction: TaxJurisdictionEnum::CzechRepublic,
			assetsAndData: [[$asset, $assetData]],
			transactionsByAsset: $transactionsByAsset,
		);

		self::assertCount(1, $result->harvestNow);
		self::assertFalse($result->harvestNow[0]->holdingVariesByBroker);
	}

	public function testHoldingVariesByBrokerIgnoresNonBuyTransactions(): void
	{
		$asset = AssetFixture::getAsset(id: 9);
		$assetData = $this->makeAssetData(firstBuyDaysAgo: 100, gainDefault: '-50', value: '950', costBasis: '1000');

		// Buy on broker 1; dividend on broker 2 — dividend shouldn't count as a separate buy broker.
		$transactionsByAsset = [
			9 => [
				TransactionFixture::getTransaction(id: 1, asset: $asset, brokerId: 1, actionType: TransactionActionTypeEnum::Buy),
				TransactionFixture::getTransaction(id: 2, asset: $asset, brokerId: 2, actionType: TransactionActionTypeEnum::Dividend),
			],
		];

		$result = $this->runOptimization(
			jurisdiction: TaxJurisdictionEnum::CzechRepublic,
			assetsAndData: [[$asset, $assetData]],
			transactionsByAsset: $transactionsByAsset,
		);

		self::assertCount(1, $result->harvestNow);
		self::assertFalse($result->harvestNow[0]->holdingVariesByBroker);
	}

	public function testGermanyPositionsBucketAsWinningShortTermRegardlessOfAge(): void
	{
		// Germany has no holding-period exemption — even multi-year holdings stay in winningShortTerm if profitable.
		$asset = AssetFixture::getAsset();
		$assetData = $this->makeAssetData(firstBuyDaysAgo: 3000, gainDefault: '5000', value: '10000', costBasis: '5000');

		$result = $this->runOptimization(jurisdiction: TaxJurisdictionEnum::Germany, assetsAndData: [[$asset, $assetData]]);

		self::assertSame(TaxJurisdictionEnum::Germany, $result->jurisdiction);
		self::assertNull($result->longTermHoldingDays);
		self::assertCount(1, $result->winningShortTerm);
		self::assertCount(0, $result->alreadyTaxFree);
		self::assertCount(0, $result->holdForTaxFreeGain);
	}

	public function testGermanyAllowanceReducesAggregateHarvestSaving(): void
	{
		$asset = AssetFixture::getAsset();
		// Loss of 5000 — without allowance: 5000 * 0.26375 = 1318.75
		// With Sparerpauschbetrag (1000 EUR) shielding: (5000 - 1000) * 0.26375 = 1055.0
		$assetData = $this->makeAssetData(firstBuyDaysAgo: 100, gainDefault: '-5000', value: '0', costBasis: '5000');

		$result = $this->runOptimization(jurisdiction: TaxJurisdictionEnum::Germany, assetsAndData: [[$asset, $assetData]]);

		self::assertCount(1, $result->harvestNow);
		self::assertSame(1055.0, $result->estimatedTaxSavedByHarvestingNow->toFloat());
		self::assertNotNull($result->annualGainExemption);
		self::assertSame(1000.0, $result->annualGainExemption->toFloat());
	}

	public function testSlovakiaPositionAboveOneYearIsTaxFree(): void
	{
		$asset = AssetFixture::getAsset();
		$assetData = $this->makeAssetData(firstBuyDaysAgo: 400, gainDefault: '1000', value: '6000', costBasis: '5000');

		$result = $this->runOptimization(jurisdiction: TaxJurisdictionEnum::Slovakia, assetsAndData: [[$asset, $assetData]]);

		self::assertSame(365, $result->longTermHoldingDays);
		self::assertCount(1, $result->alreadyTaxFree);
		self::assertCount(0, $result->winningShortTerm);
	}

	private function makeAssetData(
		int $firstBuyDaysAgo,
		string $gainDefault,
		string $value,
		string $costBasis,
		string $units = '10',
	): AssetDataDto {
		return new AssetDataDto(
			date: $this->asOf,
			price: new Decimal('100'),
			units: new Decimal($units),
			value: new Decimal($value),
			transactionValue: new Decimal($costBasis),
			transactionValueDefaultCurrency: new Decimal($costBasis),
			averagePrice: new Decimal('500'),
			averagePriceDefaultCurrency: new Decimal('500'),
			gain: new Decimal($gainDefault),
			gainDefaultCurrency: new Decimal($gainDefault),
			realizedGain: new Decimal(0),
			realizedGainDefaultCurrency: new Decimal(0),
			gainPercentage: 0.0,
			gainPercentagePerAnnum: 0.0,
			dividendYield: new Decimal(0),
			dividendYieldDefaultCurrency: new Decimal(0),
			dividendYieldPercentage: 0.0,
			dividendYieldPercentagePerAnnum: 0.0,
			fxImpact: new Decimal(0),
			fxImpactPercentage: 0.0,
			fxImpactPercentagePerAnnum: 0.0,
			return: new Decimal(0),
			returnPercentage: 0.0,
			returnPercentagePerAnnum: 0.0,
			tax: new Decimal(0),
			taxDefaultCurrency: new Decimal(0),
			fee: new Decimal(0),
			feeDefaultCurrency: new Decimal(0),
			firstTransactionActionCreated: $this->asOf->modify('-' . $firstBuyDaysAgo . ' days'),
		);
	}

	/**
	 * @param list<array{Asset, AssetDataDto}> $assetsAndData
	 * @param array<int, list<Transaction>> $transactionsByAsset
	 */
	private function runOptimization(
		TaxJurisdictionEnum $jurisdiction,
		array $assetsAndData,
		array $transactionsByAsset = [],
	): TaxOptimizationDto
	{
		$assetProvider = self::createStub(AssetProviderInterface::class);
		$assetProvider->method('getAssets')
			->willReturn(new ArrayIterator(array_map(fn(array $pair): Asset => $pair[0], $assetsAndData)));

		$assetDataProvider = self::createStub(AssetDataProviderInterface::class);
		$assetDataProvider->method('getAssetData')
			->willReturnCallback(static function (User $user, Portfolio $portfolio, Asset $asset) use ($assetsAndData): ?AssetDataDto {
				unset($user, $portfolio);
				foreach ($assetsAndData as [$pairAsset, $assetData]) {
					if ($pairAsset === $asset) {
						return $assetData;
					}
				}
				return null;
			});

		$transactionProvider = self::createStub(CurrentTransactionProviderInterface::class);
		$transactionProvider->method('loadTransactions')->willReturn($transactionsByAsset);

		$factory = new TaxJurisdictionRulesFactory(
			new CzechRepublicTaxJurisdictionRules(),
			new SlovakiaTaxJurisdictionRules(),
			new GermanyTaxJurisdictionRules(),
			new GenericTaxJurisdictionRules(),
		);

		$portfolio = PortfolioFixture::getPortfolio();
		if ($jurisdiction === TaxJurisdictionEnum::CzechRepublic) {
			$portfolio->taxJurisdiction = TaxJurisdictionEnum::CzechRepublic;
			$portfolio->estimatedTaxRate = new Decimal('0.15');
		} elseif ($jurisdiction === TaxJurisdictionEnum::Germany) {
			$portfolio->taxJurisdiction = TaxJurisdictionEnum::Germany;
			$portfolio->estimatedTaxRate = new Decimal('0.26375');
		} elseif ($jurisdiction === TaxJurisdictionEnum::Slovakia) {
			$portfolio->taxJurisdiction = TaxJurisdictionEnum::Slovakia;
			$portfolio->estimatedTaxRate = new Decimal('0.19');
		} else {
			$portfolio->taxJurisdiction = TaxJurisdictionEnum::Generic;
			$portfolio->estimatedTaxRate = null;
		}

		$calculator = new TaxOptimizationCalculator($assetProvider, $assetDataProvider, $transactionProvider, $factory);
		return $calculator->calculate(UserFixture::getUser(), $portfolio, $this->asOf);
	}
}
