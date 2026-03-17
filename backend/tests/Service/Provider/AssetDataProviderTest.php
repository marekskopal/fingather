<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Provider;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Service\Cache\Cache;
use FinGather\Service\Cache\CacheFactoryInterface;
use FinGather\Service\DataCalculator\AssetDataCalculatorInterface;
use FinGather\Service\DataCalculator\Dto\AssetDataDto;
use FinGather\Service\Provider\AssetDataProvider;
use FinGather\Tests\Fixtures\Model\Entity\AssetFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use Nette\Caching\Storage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

#[CoversClass(AssetDataProvider::class)]
#[UsesClass(Cache::class)]
#[UsesClass(AssetDataDto::class)]
final class AssetDataProviderTest extends TestCase
{
	private AssetDataCalculatorInterface&Stub $assetDataCalculator;

	private AssetDataProvider $assetDataProvider;

	protected function setUp(): void
	{
		$this->assetDataCalculator = $this::createStub(AssetDataCalculatorInterface::class);

		$storage = $this::createStub(Storage::class);
		$cache = new Cache($storage, 'test-asset-data');

		$cacheFactory = $this::createStub(CacheFactoryInterface::class);
		$cacheFactory->method('create')->willReturn($cache);

		$this->assetDataProvider = new AssetDataProvider($this->assetDataCalculator, $cacheFactory);
	}

	public function testGetAssetDataCacheMissReturnsCalculatorResult(): void
	{
		$assetData = $this->makeAssetDataDto();
		$this->assetDataCalculator->method('calculate')->willReturn($assetData);

		$result = $this->assetDataProvider->getAssetData(
			UserFixture::getUser(),
			PortfolioFixture::getPortfolio(),
			AssetFixture::getAsset(),
			new DateTimeImmutable(),
		);

		self::assertSame($assetData, $result);
	}

	public function testGetAssetDataCalculatorReturnsNullReturnsNull(): void
	{
		$this->assetDataCalculator->method('calculate')->willReturn(null);

		$result = $this->assetDataProvider->getAssetData(
			UserFixture::getUser(),
			PortfolioFixture::getPortfolio(),
			AssetFixture::getAsset(),
			new DateTimeImmutable(),
		);

		self::assertNull($result);
	}

	public function testDeleteAssetDataCompletesWithoutError(): void
	{
		$this->assetDataProvider->deleteAssetData(
			UserFixture::getUser(),
			PortfolioFixture::getPortfolio(),
			new DateTimeImmutable(),
		);

		// No exception means success
		$this->expectNotToPerformAssertions();
	}

	private function makeAssetDataDto(): AssetDataDto
	{
		$zero = new Decimal('0');
		return new AssetDataDto(
			date: new DateTimeImmutable(),
			price: new Decimal('100'),
			units: new Decimal('10'),
			value: new Decimal('1000'),
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
