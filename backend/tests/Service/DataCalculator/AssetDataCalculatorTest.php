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
		$assetDataCalculator = $this->createAssetDataCalculator(
			transactions: [],
			splits: [],
			lastTickerData: TickerDataFixture::getTickerData(),
			exchangeRate: new Decimal(1),
		);

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
				'exchangeRate' => 1.0,
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
				'exchangeRate' => 1.0,
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
				'exchangeRate' => 1.0,
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
				'exchangeRate' => 1.0,
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
				'exchangeRate' => 1.0,
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
			'Real data: Tesla' => [
				'transactions' => [
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2020-07-27'),
						units: new Decimal('0.02628947'),
						price: new Decimal('1540.16'),
						priceTickerCurrency: new Decimal('1540.16'),
						priceDefaultCurrency: new Decimal('34300.90'),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Sell,
						actionCreated: new DateTimeImmutable('2020-10-14'),
						units: new Decimal('-0.13144735'),
						price: new Decimal('449.31'),
						priceTickerCurrency: new Decimal('449.31'),
						priceDefaultCurrency: new Decimal('10413.21'),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2021-01-13'),
						units: new Decimal('0.23817746'),
						price: new Decimal('839.71'),
						priceTickerCurrency: new Decimal('839.71'),
						priceDefaultCurrency: new Decimal('18047.89'),
					),

					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2021-02-23'),
						units: new Decimal('0.15477170'),
						price: new Decimal('703.72'),
						priceTickerCurrency: new Decimal('703.72'),
						priceDefaultCurrency: new Decimal('14974.46'),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2021-02-26'),
						units: new Decimal('0.17383150'),
						price: new Decimal('665.35'),
						priceTickerCurrency: new Decimal('665.35'),
						priceDefaultCurrency: new Decimal('14415.47'),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Sell,
						actionCreated: new DateTimeImmutable('2022-03-23'),
						units: new Decimal('-0.23817746'),
						price: new Decimal('1025.41'),
						priceTickerCurrency: new Decimal('1025.41'),
						priceDefaultCurrency: new Decimal('22957.90'),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2022-05-18'),
						units: new Decimal('0.03300000'),
						price: new Decimal('723.46'),
						priceTickerCurrency: new Decimal('723.46'),
						priceDefaultCurrency: new Decimal('723.46'),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2022-05-20'),
						units: new Decimal('0.1'),
						price: new Decimal('648.63'),
						priceTickerCurrency: new Decimal('648.63'),
						priceDefaultCurrency: new Decimal('15107.89'),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2022-10-20'),
						units: new Decimal('0.58'),
						price: new Decimal('208.06'),
						priceTickerCurrency: new Decimal('208.06'),
						priceDefaultCurrency: new Decimal('5206.29'),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2022-12-20'),
						units: new Decimal('1'),
						price: new Decimal('147.87'),
						priceTickerCurrency: new Decimal('147.87'),
						priceDefaultCurrency: new Decimal('3367.89'),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2023-01-17'),
						units: new Decimal('0.4'),
						price: new Decimal('125.88'),
						priceTickerCurrency: new Decimal('125.88'),
						priceDefaultCurrency: new Decimal('2791.39'),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2023-04-19'),
						units: new Decimal('0.91644480'),
						price: new Decimal('182.13'),
						priceTickerCurrency: new Decimal('182.13'),
						priceDefaultCurrency: new Decimal('3894.58'),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2023-04-26'),
						units: new Decimal('1.10048320'),
						price: new Decimal('156.22'),
						priceTickerCurrency: new Decimal('156.22'),
						priceDefaultCurrency: new Decimal('3327.41'),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2024-02-05'),
						units: new Decimal('0.2'),
						price: new Decimal('176.99'),
						priceTickerCurrency: new Decimal('176.99'),
						priceDefaultCurrency: new Decimal('4112.96'),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2024-03-13'),
						units: new Decimal('1'),
						price: new Decimal('172.46'),
						priceTickerCurrency: new Decimal('172.46'),
						priceDefaultCurrency: new Decimal('3994.36'),
					),
				],
				'lastTickerDataClose' => 171.05,
				'exchangeRate' => 23.7282,
				'splits' => [
					SplitFixture::getSplit(
						date: new DateTimeImmutable('2020-08-31'),
						factor: new Decimal(5),
					),
					SplitFixture::getSplit(
						date: new DateTimeImmutable('2022-08-25'),
						factor: new Decimal(3),
					),
				],
				'units' => 6.5817376,
				'value' => 26713.355065880736,
				'transactionValue' => 1381.43418140416,
				'transactionValueDefaultCurrency' => 30265.2433481768,
				'gain' => -255.62796492416,
				'gainDefaultCurrency' => -6065.591477313454,
				'gainPercentage' => -18.5,
				'realizedGain' => 379.2733639538333,
				'realizedGainDefaultCurrency' => 8638.956009072166,
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
		float $exchangeRate,
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
			exchangeRate: new Decimal((string) $exchangeRate),
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

	private function createAssetDataCalculator(
		array $transactions,
		array $splits,
		TickerData $lastTickerData,
		Decimal $exchangeRate,
	): AssetDataCalculator
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
			->willReturn($exchangeRate);

		return new AssetDataCalculator($transactionProvider, $splitProvider, $tickerDataProvider, $exchangeRateProvider);
	}
}
