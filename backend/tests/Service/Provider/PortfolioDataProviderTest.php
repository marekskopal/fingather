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
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Service\Cache\Cache;
use FinGather\Service\Cache\CacheFactoryInterface;
use FinGather\Service\Cache\CacheTag;
use FinGather\Service\DataCalculator\DataCalculatorInterface;
use FinGather\Service\DataCalculator\Dto\AssetDataDto;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use FinGather\Service\DataCalculator\MwrCalculatorInterface;
use FinGather\Service\DataCalculator\TwrCalculatorInterface;
use FinGather\Service\Provider\AssetDataProviderInterface;
use FinGather\Service\Provider\AssetProviderInterface;
use FinGather\Service\Provider\PortfolioDataProvider;
use FinGather\Service\Provider\PortfolioDataProviderInterface;
use FinGather\Service\Provider\TransactionProviderInterface;
use FinGather\Tests\Fixtures\Model\Entity\AssetFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\TransactionFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use FinGather\Utils\DateTimeUtils;
use Nette\Caching\Storage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(PortfolioDataProvider::class)]
#[UsesClass(Cache::class)]
#[UsesClass(CalculatedDataDto::class)]
#[UsesClass(AssetDataDto::class)]
#[UsesClass(Country::class)]
#[UsesClass(Currency::class)]
#[UsesClass(Group::class)]
#[UsesClass(Industry::class)]
#[UsesClass(Market::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(Sector::class)]
#[UsesClass(Ticker::class)]
#[UsesClass(Transaction::class)]
#[UsesClass(User::class)]
#[UsesClass(CacheTag::class)]
#[UsesClass(DateTimeUtils::class)]
#[UsesClass(Asset::class)]
final class PortfolioDataProviderTest extends TestCase
{
	private DataCalculatorInterface&Stub $dataCalculator;

	private AssetProviderInterface&Stub $assetProvider;

	private AssetDataProviderInterface&Stub $assetDataProvider;

	private TransactionProviderInterface&Stub $transactionProvider;

	private PortfolioDataProviderInterface $portfolioDataProvider;

	protected function setUp(): void
	{
		$this->dataCalculator = $this::createStub(DataCalculatorInterface::class);
		$this->assetProvider = $this::createStub(AssetProviderInterface::class);
		$this->assetDataProvider = $this::createStub(AssetDataProviderInterface::class);
		$this->transactionProvider = $this::createStub(TransactionProviderInterface::class);
		// Return empty iterator by default so buildCashFlows produces no cash flows.
		$this->transactionProvider->method('getTransactions')->willReturn(new ArrayIterator([]));

		$storage = $this::createStub(Storage::class);
		$cache = new Cache($storage, 'test-portfolio-data');

		$cacheFactory = $this::createStub(CacheFactoryInterface::class);
		$cacheFactory->method('create')->willReturn($cache);

		$this->portfolioDataProvider = new PortfolioDataProvider(
			dataCalculator: $this->dataCalculator,
			assetProvider: $this->assetProvider,
			assetDataProvider: $this->assetDataProvider,
			transactionProvider: $this->transactionProvider,
			twrCalculator: $this::createStub(TwrCalculatorInterface::class),
			mwrCalculator: $this::createStub(MwrCalculatorInterface::class),
			logger: $this::createStub(LoggerInterface::class),
			cacheFactory: $cacheFactory,
		);
	}

	public function testGetPortfolioDataSingleAssetReturnsCalculatorResult(): void
	{
		$asset = AssetFixture::getAsset();
		$this->assetProvider->method('getAssets')->willReturn(new ArrayIterator([$asset]));

		$assetData = $this->makeAssetDataDto();
		$this->assetDataProvider->method('getAssetData')->willReturn($assetData);

		$transaction = TransactionFixture::getTransaction(actionCreated: new DateTimeImmutable('2020-01-01'));
		$this->transactionProvider->method('getFirstTransaction')->willReturn($transaction);

		$calculatedData = $this->makeCalculatedDataDto(new Decimal('1000'));
		$this->dataCalculator->method('calculate')->willReturn($calculatedData);

		$result = $this->portfolioDataProvider->getPortfolioData(
			UserFixture::getUser(),
			PortfolioFixture::getPortfolio(),
			new DateTimeImmutable(),
		);

		self::assertSame($calculatedData, $result);
	}

	public function testGetPortfolioDataAssetDataNullSkipsAsset(): void
	{
		$asset = AssetFixture::getAsset();
		$this->assetProvider->method('getAssets')->willReturn(new ArrayIterator([$asset]));

		$this->assetDataProvider->method('getAssetData')->willReturn(null);

		$transaction = TransactionFixture::getTransaction();
		$this->transactionProvider->method('getFirstTransaction')->willReturn($transaction);

		$calculatedData = $this->makeCalculatedDataDto(new Decimal('0'));
		$this->dataCalculator->method('calculate')->willReturn($calculatedData);

		$result = $this->portfolioDataProvider->getPortfolioData(
			UserFixture::getUser(),
			PortfolioFixture::getPortfolio(),
			new DateTimeImmutable(),
		);

		self::assertSame($calculatedData, $result);
	}

	public function testGetPortfolioDataNoAssetsReturnsCalculatorResult(): void
	{
		$this->assetProvider->method('getAssets')->willReturn(new ArrayIterator([]));
		$this->transactionProvider->method('getFirstTransaction')->willReturn(null);

		$calculatedData = $this->makeCalculatedDataDto(new Decimal('0'));
		$this->dataCalculator->method('calculate')->willReturn($calculatedData);

		$result = $this->portfolioDataProvider->getPortfolioData(
			UserFixture::getUser(),
			PortfolioFixture::getPortfolio(),
			new DateTimeImmutable(),
		);

		self::assertSame($calculatedData, $result);
	}

	public function testDeletePortfolioDataCompletesWithoutError(): void
	{
		$this->portfolioDataProvider->deletePortfolioData(
			UserFixture::getUser(),
			PortfolioFixture::getPortfolio(),
			new DateTimeImmutable(),
		);

		$this->expectNotToPerformAssertions();
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
