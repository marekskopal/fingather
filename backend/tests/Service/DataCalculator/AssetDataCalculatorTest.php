<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\DataCalculator;

use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Country;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Market;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Split;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\TickerData;
use FinGather\Model\Entity\TickerIndustry;
use FinGather\Model\Entity\TickerSector;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\AssetDataCalculator;
use FinGather\Service\DataCalculator\Dto\AssetDataDto;
use FinGather\Service\DataCalculator\Dto\TransactionBuyDto;
use FinGather\Service\DataCalculator\Dto\TransactionValueDto;
use FinGather\Service\DataCalculator\Dto\ValueDto;
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
use FinGather\Utils\CalculatorUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable;

#[CoversClass(AssetDataCalculator::class)]
#[UsesClass(Asset::class)]
#[UsesClass(Country::class)]
#[UsesClass(Currency::class)]
#[UsesClass(Group::class)]
#[UsesClass(Market::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(Split::class)]
#[UsesClass(Ticker::class)]
#[UsesClass(TickerIndustry::class)]
#[UsesClass(TickerSector::class)]
#[UsesClass(TickerData::class)]
#[UsesClass(Transaction::class)]
#[UsesClass(User::class)]
#[UsesClass(AssetDataDto::class)]
#[UsesClass(TransactionBuyDto::class)]
#[UsesClass(ValueDto::class)]
#[UsesClass(CalculatorUtils::class)]
#[UsesClass(TransactionValueDto::class)]
final class AssetDataCalculatorTest extends TestCase
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

		self::assertNull($assetDataCalculator->calculate(
			$user,
			$portfolio,
			$asset,
			$dateTime,
		));
	}

	/**
	 * @return array<string, array{
	 *     transactions: Transaction[],
	 *     splits: Split[],
	 *     lastTickerDataClose: float,
	 *     exchangeRate: float,
	 *     price: float,
	 *     units: float,
	 *     value: float,
	 *     transactionValue: float,
	 *     transactionValueDefaultCurrency: float,
	 *     averagePrice: float,
	 *     averagePriceDefaultCurrency: float,
	 *     gain: float,
	 *     gainDefaultCurrency: float,
	 *     gainPercentage: float,
	 *     realizedGain: float,
	 *     realizedGainDefaultCurrency: float,
	 *     dividendGain: float,
	 *     dividendGainDefaultCurrency: float,
	 * }>
	 */
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
				'price' => 10.0,
				'units' => 0.0,
				'value' => 0.0,
				'transactionValue' => 0.0,
				'transactionValueDefaultCurrency' => 0.0,
				'averagePrice' => 0.0,
				'averagePriceDefaultCurrency' => 0.0,
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
				'price' => 10.0,
				'units' => 0.0,
				'value' => 0.0,
				'transactionValue' => 0.0,
				'transactionValueDefaultCurrency' => 0.0,
				'averagePrice' => 0.0,
				'averagePriceDefaultCurrency' => 0.0,
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
				'price' => 5.0,
				'units' => 2.0,
				'value' => 10.0,
				'transactionValue' => 10.0,
				'transactionValueDefaultCurrency' => 10.0,
				'averagePrice' => 5.0,
				'averagePriceDefaultCurrency' => 5.0,
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
					SplitFixture::getSplit(
						date: new DateTimeImmutable('2024-01-05'),
						factor: new Decimal(2),
					),
				],
				'lastTickerDataClose' => 7.5,
				'exchangeRate' => 1.0,
				'price' => 7.5,
				'units' => 0.0,
				'value' => 0.0,
				'transactionValue' => 0.0,
				'transactionValueDefaultCurrency' => 0.0,
				'averagePrice' => 0.0,
				'averagePriceDefaultCurrency' => 0.0,
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
				'price' => 7.5,
				'units' => 2.0,
				'value' => 15.0,
				'transactionValue' => 10.0,
				'transactionValueDefaultCurrency' => 10.0,
				'averagePrice' => 5.0,
				'averagePriceDefaultCurrency' => 5.0,
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
						brokerId: 2,
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2020-07-27'),
						units: new Decimal('0.02628947', 18),
						price: new Decimal('1540.16'),
						priceTickerCurrency: new Decimal('1540.16'),
						priceDefaultCurrency: new Decimal('34300.90'),
					),
					TransactionFixture::getTransaction(
						brokerId: 2,
						actionType: TransactionActionTypeEnum::Sell,
						actionCreated: new DateTimeImmutable('2020-10-14'),
						units: new Decimal('-0.13144735', 18),
						price: new Decimal('449.31'),
						priceTickerCurrency: new Decimal('449.31'),
						priceDefaultCurrency: new Decimal('10413.21'),
					),
					TransactionFixture::getTransaction(
						brokerId: 2,
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2021-01-13'),
						units: new Decimal('0.23817746', 18),
						price: new Decimal('839.71'),
						priceTickerCurrency: new Decimal('839.71'),
						priceDefaultCurrency: new Decimal('18047.89'),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2021-02-23'),
						units: new Decimal('0.15477170', 18),
						price: new Decimal('703.72'),
						priceTickerCurrency: new Decimal('703.72'),
						priceDefaultCurrency: new Decimal('14974.46'),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2021-02-26'),
						units: new Decimal('0.17383150', 18),
						price: new Decimal('665.35'),
						priceTickerCurrency: new Decimal('665.35'),
						priceDefaultCurrency: new Decimal('14415.47'),
					),
					TransactionFixture::getTransaction(
						brokerId: 2,
						actionType: TransactionActionTypeEnum::Sell,
						actionCreated: new DateTimeImmutable('2022-03-23'),
						units: new Decimal('-0.23817746', 18),
						price: new Decimal('1025.41'),
						priceTickerCurrency: new Decimal('1025.41'),
						priceDefaultCurrency: new Decimal('22957.90'),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2022-05-18'),
						units: new Decimal('0.03300000', 18),
						price: new Decimal('723.46'),
						priceTickerCurrency: new Decimal('723.46'),
						priceDefaultCurrency: new Decimal('17042.55'),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2022-05-20'),
						units: new Decimal('0.1', 18),
						price: new Decimal('648.63'),
						priceTickerCurrency: new Decimal('648.63'),
						priceDefaultCurrency: new Decimal('15107.89'),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2022-10-20'),
						units: new Decimal('0.58', 18),
						price: new Decimal('208.06'),
						priceTickerCurrency: new Decimal('208.06'),
						priceDefaultCurrency: new Decimal('5206.29'),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2022-12-20'),
						units: new Decimal('1', 18),
						price: new Decimal('147.87'),
						priceTickerCurrency: new Decimal('147.87'),
						priceDefaultCurrency: new Decimal('3367.89'),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2023-01-17'),
						units: new Decimal('0.4', 18),
						price: new Decimal('125.88'),
						priceTickerCurrency: new Decimal('125.88'),
						priceDefaultCurrency: new Decimal('2791.39'),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2023-04-19'),
						units: new Decimal('0.91644480', 18),
						price: new Decimal('182.13'),
						priceTickerCurrency: new Decimal('182.13'),
						priceDefaultCurrency: new Decimal('3894.58'),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2023-04-26'),
						units: new Decimal('1.10048320', 18),
						price: new Decimal('156.22'),
						priceTickerCurrency: new Decimal('156.22'),
						priceDefaultCurrency: new Decimal('3327.41'),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2024-02-05'),
						units: new Decimal('0.2', 18),
						price: new Decimal('176.99'),
						priceTickerCurrency: new Decimal('176.99'),
						priceDefaultCurrency: new Decimal('4112.96'),
					),
					TransactionFixture::getTransaction(
						actionType: TransactionActionTypeEnum::Buy,
						actionCreated: new DateTimeImmutable('2024-03-13'),
						units: new Decimal('1', 18),
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
				'price' => 171.05,
				'units' => 6.5817376,
				'value' => 26713.355065880736,
				'transactionValue' => 1178.896286177,
				'transactionValueDefaultCurrency' => 26448.651147783,
				'averagePrice' => 189.39363636363638,
				'averagePriceDefaultCurrency' => 4291.666969696969,
				'gain' => -53.090069697,
				'gainDefaultCurrency' => -1259.7317917843554,
				'gainPercentage' => -4.5,
				'realizedGain' => 62.8001730353,
				'realizedGainDefaultCurrency' => 1636.4900883451,
				'dividendGain' => 0.0,
				'dividendGainDefaultCurrency' => 0.0,
			],
		];
	}

	/**
	 * @param list<Transaction> $transactions
	 * @param list<Split> $splits
	 */
	#[DataProvider('calculateDataProvider')]
	public function testCalculate(
		array $transactions,
		array $splits,
		float $lastTickerDataClose,
		float $exchangeRate,
		float $price,
		float $units,
		float $value,
		float $transactionValue,
		float $transactionValueDefaultCurrency,
		float $averagePrice,
		float $averagePriceDefaultCurrency,
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

		self::assertInstanceOf(AssetDataDto::class, $assetData);

		self::assertSame($price, $assetData->price->toFloat());
		self::assertSame($units, $assetData->units->toFloat());
		self::assertSame($value, $assetData->value->toFloat());
		self::assertSame($transactionValue, $assetData->transactionValue->toFloat());
		self::assertSame($transactionValueDefaultCurrency, $assetData->transactionValueDefaultCurrency->toFloat());
		self::assertSame($averagePrice, $assetData->averagePrice->toFloat());
		self::assertSame($averagePriceDefaultCurrency, $assetData->averagePriceDefaultCurrency->toFloat());
		self::assertSame($gain, $assetData->gain->toFloat());
		self::assertSame($gainDefaultCurrency, $assetData->gainDefaultCurrency->toFloat());
		self::assertSame($gainPercentage, $assetData->gainPercentage);
		self::assertSame($realizedGain, $assetData->realizedGain->toFloat());
		self::assertSame($realizedGainDefaultCurrency, $assetData->realizedGainDefaultCurrency->toFloat());
		self::assertSame($dividendGain, $assetData->dividendGain->toFloat());
		self::assertSame($dividendGainDefaultCurrency, $assetData->dividendGainDefaultCurrency->toFloat());
	}

	/**
	 * @param list<Transaction> $transactions
	 * @param list<Split> $splits
	 */
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

		$tickerDataProvider = self::createStub(TickerDataProvider::class);
		$tickerDataProvider->method('getLastTickerData')
			->willReturn($lastTickerData);

		$exchangeRateProvider = $this->createMock(ExchangeRateProvider::class);
		$exchangeRateProvider->method('getExchangeRate')
			->willReturn($exchangeRate);

		return new AssetDataCalculator($transactionProvider, $splitProvider, $tickerDataProvider, $exchangeRateProvider);
	}
}
