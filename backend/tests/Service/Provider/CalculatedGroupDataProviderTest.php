<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Provider;

use ArrayIterator;
use DateTimeImmutable;
use Decimal\Decimal;
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
use FinGather\Service\DataCalculator\DataCalculatorInterface;
use FinGather\Service\DataCalculator\Dto\AssetDataDto;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use FinGather\Service\Provider\AssetDataProviderInterface;
use FinGather\Service\Provider\AssetProviderInterface;
use FinGather\Service\Provider\CalculatedGroupDataProvider;
use FinGather\Tests\Fixtures\Model\Entity\AssetFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use FinGather\Utils\DateTimeUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CalculatedGroupDataProvider::class)]
#[UsesClass(Asset::class)]
#[UsesClass(AssetDataDto::class)]
#[UsesClass(CalculatedDataDto::class)]
#[UsesClass(Country::class)]
#[UsesClass(Currency::class)]
#[UsesClass(Group::class)]
#[UsesClass(Industry::class)]
#[UsesClass(Market::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(Sector::class)]
#[UsesClass(Ticker::class)]
#[UsesClass(User::class)]
#[UsesClass(DateTimeUtils::class)]
final class CalculatedGroupDataProviderTest extends TestCase
{
	private User $user;

	private Portfolio $portfolio;

	protected function setUp(): void
	{
		$this->user = UserFixture::getUser();
		$this->portfolio = PortfolioFixture::getPortfolio();
	}

	public function testEmptyAssetIteratorPassesEmptyArrayToCalculator(): void
	{
		$expected = $this->makeCalculatedDataDto(new Decimal('0'));

		$capturedAssets = null;
		$dataCalculator = self::createStub(DataCalculatorInterface::class);
		$dataCalculator->method('calculate')->willReturnCallback(
			function (array $assets) use (&$capturedAssets, $expected): CalculatedDataDto {
				$capturedAssets = $assets;
				return $expected;
			},
		);

		$assetProvider = self::createStub(AssetProviderInterface::class);
		$assetProvider->method('getAssets')->willReturn(new ArrayIterator([]));

		$assetDataProvider = self::createStub(AssetDataProviderInterface::class);

		$provider = new CalculatedGroupDataProvider($dataCalculator, $assetProvider, $assetDataProvider);
		$result = $provider->getCalculatedData($this->user, $this->portfolio, new DateTimeImmutable());

		self::assertSame($expected, $result);
		self::assertSame([], $capturedAssets);
	}

	public function testNullAssetDataIsSkipped(): void
	{
		// Two assets — only the second has data; calculator should receive a single AssetDataDto.
		$asset1 = AssetFixture::getAsset(id: 1);
		$asset2 = AssetFixture::getAsset(id: 2);
		$asset2Data = $this->makeAssetDataDto(new Decimal('500'), new DateTimeImmutable('2024-01-15'));

		$assetProvider = self::createStub(AssetProviderInterface::class);
		$assetProvider->method('getAssets')->willReturn(new ArrayIterator([$asset1, $asset2]));

		$assetDataProvider = self::createStub(AssetDataProviderInterface::class);
		$assetDataProvider->method('getAssetData')->willReturnCallback(
			function (User $user, Portfolio $portfolio, Asset $asset) use ($asset2, $asset2Data): ?AssetDataDto {
				return $asset->id === $asset2->id ? $asset2Data : null;
			},
		);

		$capturedAssets = [];
		$dataCalculator = self::createStub(DataCalculatorInterface::class);
		$dataCalculator->method('calculate')->willReturnCallback(
			function (array $assets) use (&$capturedAssets): CalculatedDataDto {
				$capturedAssets = $assets;
				return $this->makeCalculatedDataDto(new Decimal('500'));
			},
		);

		$provider = new CalculatedGroupDataProvider($dataCalculator, $assetProvider, $assetDataProvider);
		$provider->getCalculatedData($this->user, $this->portfolio, new DateTimeImmutable('2024-12-31'));

		self::assertCount(1, $capturedAssets);
		self::assertSame($asset2Data, $capturedAssets[0]);
	}

	public function testFirstTransactionActionCreatedTracksEarliestAcrossAssets(): void
	{
		$asset1 = AssetFixture::getAsset(id: 1);
		$asset2 = AssetFixture::getAsset(id: 2);

		// asset1's first action is older than asset2's; calculator should receive the older one.
		$earlier = new DateTimeImmutable('2023-05-10');
		$later = new DateTimeImmutable('2024-08-22');
		$asset1Data = $this->makeAssetDataDto(new Decimal('100'), $later);
		$asset2Data = $this->makeAssetDataDto(new Decimal('200'), $earlier);

		$assetProvider = self::createStub(AssetProviderInterface::class);
		$assetProvider->method('getAssets')->willReturn(new ArrayIterator([$asset1, $asset2]));

		$assetDataProvider = self::createStub(AssetDataProviderInterface::class);
		$assetDataProvider->method('getAssetData')->willReturnCallback(
			function (User $user, Portfolio $portfolio, Asset $asset) use ($asset2, $asset1Data, $asset2Data): AssetDataDto {
				return $asset->id === $asset2->id ? $asset2Data : $asset1Data;
			},
		);

		$capturedFirst = null;
		$dataCalculator = self::createStub(DataCalculatorInterface::class);
		$dataCalculator->method('calculate')->willReturnCallback(
			function (array $assets, DateTimeImmutable $dateTime, DateTimeImmutable $firstTx) use (&$capturedFirst): CalculatedDataDto {
				$capturedFirst = $firstTx;
				return $this->makeCalculatedDataDto(new Decimal('300'));
			},
		);

		$provider = new CalculatedGroupDataProvider($dataCalculator, $assetProvider, $assetDataProvider);
		$provider->getCalculatedData($this->user, $this->portfolio, new DateTimeImmutable('2024-12-31'));

		self::assertEquals($earlier, $capturedFirst);
	}

	public function testEndOfDayDateIsForwardedToCalculator(): void
	{
		// setEndOfDateTime should pin the time to end-of-day before passing to calculator.
		$asset = AssetFixture::getAsset(id: 1);
		$assetData = $this->makeAssetDataDto(new Decimal('100'), new DateTimeImmutable('2024-01-01'));

		$assetProvider = self::createStub(AssetProviderInterface::class);
		$assetProvider->method('getAssets')->willReturn(new ArrayIterator([$asset]));

		$assetDataProvider = self::createStub(AssetDataProviderInterface::class);
		$assetDataProvider->method('getAssetData')->willReturn($assetData);

		$capturedDateTime = null;
		$dataCalculator = self::createStub(DataCalculatorInterface::class);
		$dataCalculator->method('calculate')->willReturnCallback(
			function (array $assets, DateTimeImmutable $dateTime) use (&$capturedDateTime): CalculatedDataDto {
				$capturedDateTime = $dateTime;
				return $this->makeCalculatedDataDto(new Decimal('100'));
			},
		);

		$provider = new CalculatedGroupDataProvider($dataCalculator, $assetProvider, $assetDataProvider);
		$provider->getCalculatedData($this->user, $this->portfolio, new DateTimeImmutable('2024-06-15 10:30:00'));

		self::assertNotNull($capturedDateTime);
		self::assertSame('2024-06-15 23:59:59', $capturedDateTime->format('Y-m-d H:i:s'));
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

	private function makeAssetDataDto(Decimal $value, DateTimeImmutable $firstTransactionActionCreated): AssetDataDto
	{
		$zero = new Decimal('0');
		return new AssetDataDto(
			date: new DateTimeImmutable(),
			price: $zero,
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
			firstTransactionActionCreated: $firstTransactionActionCreated,
		);
	}
}
