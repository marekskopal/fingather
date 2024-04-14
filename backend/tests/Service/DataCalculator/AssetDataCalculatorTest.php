<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\DataCalculator;

use Decimal\Decimal;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\TickerData;
use FinGather\Service\DataCalculator\AssetDataCalculator;
use FinGather\Service\Provider\ExchangeRateProvider;
use FinGather\Service\Provider\SplitProvider;
use FinGather\Service\Provider\TickerDataProvider;
use FinGather\Service\Provider\TransactionProvider;
use FinGather\Tests\Fixtures\Model\Entity\AssetFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\SplitFixture;
use FinGather\Tests\Fixtures\Model\Entity\TickerDataFixture;
use FinGather\Tests\Fixtures\Model\Entity\TransactionFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable;

#[CoversClass(AssetDataCalculator::class)]
class AssetDataCalculatorTest extends TestCase
{
	public function testCalculateNull(): void
	{
		$assetDataCalculator = $this->createAssetDataCalculator([], [], TickerDataFixture::getTickerData());

		$user = UserFixture::getUser();
		$portfolio = PortfolioFixture::getPortfolio();
		$asset = AssetFixture::getAsset();
		$dateTime = new DateTimeImmutable();

		$this->assertNull($assetDataCalculator->calculate(
			$user,
			$portfolio,
			$asset,
			$dateTime,
		));
	}

	public static function calculateDataProvider(): array
	{
		return [
			'one buy, one sell (zero gain)' => [
				'transactions' => [
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						units: new Decimal(1),
						price: new Decimal(10),
						priceTickerCurrency: new Decimal(10),
						priceDefaultCurrency: new Decimal(10),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Sell,
						units: new Decimal(-1),
						price: new Decimal(10),
						priceTickerCurrency: new Decimal(10),
						priceDefaultCurrency: new Decimal(10),
					),
				],
				'splits' => [],
				'lastTickerDataClose' => 10.0,
				'units' => 0.0,
				'value' => 0.0,
				'transactionValue' => 0.0,
				'transactionValueDefaultCurrency' => 0.0,
				'gain' => 0.0,
				'gainDefaultCurrency' => 0.0,
				'gainPercentage' => 0.0,
				'realizedGain' => 0.0,
				'realizedGainDefaultCurrency' => 0.0,
				'dividendGain' => 0.0,
				'dividendGainDefaultCurrency' => 0.0,
			],
			'two buy, one sell with split (zero gain)' => [
				'transactions' => [
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2024-01-01'),
						units: new Decimal(1),
						price: new Decimal(10),
						priceTickerCurrency: new Decimal(10),
						priceDefaultCurrency: new Decimal(10),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2024-01-02'),
						units: new Decimal(1),
						price: new Decimal(10),
						priceTickerCurrency: new Decimal(10),
						priceDefaultCurrency: new Decimal(10),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Sell,
						actionCreated: new DateTimeImmutable('2024-01-04'),
						units: new Decimal(-4),
						price: new Decimal(5),
						priceTickerCurrency: new Decimal(5),
						priceDefaultCurrency: new Decimal(5),
					),
				],
				'splits' => [
					SplitFixture::getSplit(
						date: new DateTimeImmutable('2024-01-03'),
						factor: new Decimal(2),
					),
				],
				'lastTickerDataClose' => 10.0,
				'units' => 0.0,
				'value' => 0.0,
				'transactionValue' => 0.0,
				'transactionValueDefaultCurrency' => 0.0,
				'gain' => 0.0,
				'gainDefaultCurrency' => 0.0,
				'gainPercentage' => 0.0,
				'realizedGain' => 0.0,
				'realizedGainDefaultCurrency' => 0.0,
				'dividendGain' => 0.0,
				'dividendGainDefaultCurrency' => 0.0,
			],
			'two buy, one sell with split (zero gain, must stay second buy)' => [
				'transactions' => [
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2024-01-01'),
						units: new Decimal(1),
						price: new Decimal(10),
						priceTickerCurrency: new Decimal(10),
						priceDefaultCurrency: new Decimal(10),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2024-01-02'),
						units: new Decimal(1),
						price: new Decimal(10),
						priceTickerCurrency: new Decimal(10),
						priceDefaultCurrency: new Decimal(10),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Sell,
						actionCreated: new DateTimeImmutable('2024-01-04'),
						units: new Decimal(-2),
						price: new Decimal(5),
						priceTickerCurrency: new Decimal(5),
						priceDefaultCurrency: new Decimal(5),
					),
				],
				'lastTickerDataClose' => 5.0,
				'splits' => [
					SplitFixture::getSplit(
						date: new DateTimeImmutable('2024-01-03'),
						factor: new Decimal(2),
					),
				],
				'units' => 2.0,
				'value' => 10.0,
				'transactionValue' => 10.0,
				'transactionValueDefaultCurrency' => 10.0,
				'gain' => 0.0,
				'gainDefaultCurrency' => 0.0,
				'gainPercentage' => 0.0,
				'realizedGain' => 0.0,
				'realizedGainDefaultCurrency' => 0.0,
				'dividendGain' => 0.0,
				'dividendGainDefaultCurrency' => 0.0,
			],
			'two buy, one sell with split (ten realized gain)' => [
				'transactions' => [
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2024-01-01'),
						units: new Decimal(1),
						price: new Decimal(10),
						priceTickerCurrency: new Decimal(10),
						priceDefaultCurrency: new Decimal(10),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2024-01-02'),
						units: new Decimal(1),
						price: new Decimal(10),
						priceTickerCurrency: new Decimal(10),
						priceDefaultCurrency: new Decimal(10),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Sell,
						actionCreated: new DateTimeImmutable('2024-01-04'),
						units: new Decimal(-4),
						price: new Decimal('7.5'),
						priceTickerCurrency: new Decimal('7.5'),
						priceDefaultCurrency: new Decimal('7.5'),
					),
				],
				'splits' => [
					SplitFixture::getSplit(
						date: new DateTimeImmutable('2024-01-03'),
						factor: new Decimal(2),
					),
				],
				'lastTickerDataClose' => 7.5,
				'units' => 0.0,
				'value' => 0.0,
				'transactionValue' => 0.0,
				'transactionValueDefaultCurrency' => 0.0,
				'gain' => 0.0,
				'gainDefaultCurrency' => 0.0,
				'gainPercentage' => 0.0,
				'realizedGain' => 10.0,
				'realizedGainDefaultCurrency' => 10.0,
				'dividendGain' => 0.0,
				'dividendGainDefaultCurrency' => 0.0,
			],
			'two buy, one sell with split (ten gain, must stay second buy)' => [
				'transactions' => [
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2024-01-01'),
						units: new Decimal(1),
						price: new Decimal(10),
						priceTickerCurrency: new Decimal(10),
						priceDefaultCurrency: new Decimal(10),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2024-01-02'),
						units: new Decimal(1),
						price: new Decimal(10),
						priceTickerCurrency: new Decimal(10),
						priceDefaultCurrency: new Decimal(10),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Sell,
						actionCreated: new DateTimeImmutable('2024-01-04'),
						units: new Decimal(-2),
						price: new Decimal('7.5'),
						priceTickerCurrency: new Decimal('7.5'),
						priceDefaultCurrency: new Decimal('7.5'),
					),
				],
				'lastTickerDataClose' => 7.5,
				'splits' => [
					SplitFixture::getSplit(
						date: new DateTimeImmutable('2024-01-03'),
						factor: new Decimal(2),
					),
				],
				'units' => 2.0,
				'value' => 15.0,
				'transactionValue' => 10.0,
				'transactionValueDefaultCurrency' => 10.0,
				'gain' => 5.0,
				'gainDefaultCurrency' => 5.0,
				'gainPercentage' => 50.0,
				'realizedGain' => 5.0,
				'realizedGainDefaultCurrency' => 5.0,
				'dividendGain' => 0.0,
				'dividendGainDefaultCurrency' => 0.0,
			],
		];
	}

	#[DataProvider('calculateDataProvider')]
	public function testCalculate(
		array $transactions,
		array $splits,
		float $lastTickerDataClose,
		float $units,
		float $value,
		float $transactionValue,
		float $transactionValueDefaultCurrency,
		float $gain,
		float $gainDefaultCurrency,
		float $gainPercentage,
		float $realizedGain,
		float $realizedGainDefaultCurrency,
		float $dividendGain,
		float $dividendGainDefaultCurrency,
	): void
	{
		$assetDataCalculator = $this->createAssetDataCalculator(
			transactions: $transactions,
			splits: $splits,
			lastTickerData: TickerDataFixture::getTickerData(
				close: new Decimal((string) $lastTickerDataClose),
			),
		);

		$user = UserFixture::getUser();
		$portfolio = PortfolioFixture::getPortfolio();
		$asset = AssetFixture::getAsset();
		$dateTime = new DateTimeImmutable();

		$assetData = $assetDataCalculator->calculate($user, $portfolio, $asset, $dateTime);

		$this->assertSame($units, $assetData->units->toFloat());
		$this->assertSame($value, $assetData->value->toFloat());
		$this->assertSame($transactionValue, $assetData->transactionValue->toFloat());
		$this->assertSame($transactionValueDefaultCurrency, $assetData->transactionValueDefaultCurrency->toFloat());
		$this->assertSame($gain, $assetData->gain->toFloat());
		$this->assertSame($gainDefaultCurrency, $assetData->gainDefaultCurrency->toFloat());
		$this->assertSame($gainPercentage, $assetData->gainPercentage);
		$this->assertSame($realizedGain, $assetData->realizedGain->toFloat());
		$this->assertSame($realizedGainDefaultCurrency, $assetData->realizedGainDefaultCurrency->toFloat());
		$this->assertSame($dividendGain, $assetData->dividendGain->toFloat());
		$this->assertSame($dividendGainDefaultCurrency, $assetData->dividendGainDefaultCurrency->toFloat());
	}

	private function createAssetDataCalculator(array $transactions, array $splits, TickerData $lastTickerData): AssetDataCalculator
	{
		$transactionProvider = $this->createMock(TransactionProvider::class);
		$transactionProvider->method('getTransactions')
			->willReturn($transactions);

		$splitProvider = $this->createMock(SplitProvider::class);
		$splitProvider->method('getSplits')
			->willReturn($splits);

		$tickerDataProvider = $this->createStub(TickerDataProvider::class);
		$tickerDataProvider->method('getLastTickerData')
			->willReturn($lastTickerData);

		$exchangeRateProvider = $this->createMock(ExchangeRateProvider::class);
		$exchangeRateProvider->method('getExchangeRate')
			->willReturn(new Decimal(1));

		return new AssetDataCalculator($transactionProvider, $splitProvider, $tickerDataProvider, $exchangeRateProvider);
	}
}
