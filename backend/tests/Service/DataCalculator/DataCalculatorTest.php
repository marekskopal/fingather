<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\DataCalculator;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Service\DataCalculator\DataCalculator;
use FinGather\Service\DataCalculator\Dto\AssetDataDto;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use FinGather\Utils\CalculatorUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DataCalculator::class)]
#[UsesClass(AssetDataDto::class)]
#[UsesClass(CalculatedDataDto::class)]
#[UsesClass(CalculatorUtils::class)]
final class DataCalculatorTest extends TestCase
{
	private DataCalculator $calculator;

	protected function setUp(): void
	{
		$this->calculator = new DataCalculator();
	}

	public function testCalculateEmptyAssets(): void
	{
		$now = new DateTimeImmutable('2024-01-01');

		$result = $this->calculator->calculate([], $now, $now);

		self::assertSame(0.0, $result->value->toFloat());
		self::assertSame(0.0, $result->transactionValue->toFloat());
		self::assertSame(0.0, $result->gain->toFloat());
		self::assertSame(0.0, $result->gainPercentage);
		self::assertSame(0.0, $result->gainPercentagePerAnnum);
		self::assertSame(0.0, $result->dividendYield->toFloat());
		self::assertSame(0.0, $result->dividendYieldPercentage);
		self::assertSame(0.0, $result->dividendYieldPercentagePerAnnum);
		self::assertSame(0.0, $result->fxImpact->toFloat());
		self::assertSame(0.0, $result->fxImpactPercentage);
		self::assertSame(0.0, $result->fxImpactPercentagePerAnnum);
		self::assertSame(0.0, $result->return->toFloat());
		self::assertSame(0.0, $result->returnPercentage);
		self::assertSame(0.0, $result->returnPercentagePerAnnum);
		self::assertSame(0.0, $result->realizedGain->toFloat());
		self::assertSame(0.0, $result->tax->toFloat());
		self::assertSame(0.0, $result->fee->toFloat());
	}

	public function testCalculateSingleAsset365Days(): void
	{
		// 2022-01-01 to 2023-01-01 = exactly 365 days (2022 is not a leap year)
		$dateTime = new DateTimeImmutable('2023-01-01');
		$firstTransaction = new DateTimeImmutable('2022-01-01');

		$asset = $this->makeAssetData(
			value: new Decimal(200),
			transactionValueDefaultCurrency: new Decimal(100),
			gainDefaultCurrency: new Decimal(20),
		);

		$result = $this->calculator->calculate([$asset], $dateTime, $firstTransaction);

		self::assertSame(200.0, $result->value->toFloat());
		self::assertSame(100.0, $result->transactionValue->toFloat());
		self::assertSame(20.0, $result->gain->toFloat());
		// 20/100*100 = 20.0
		self::assertSame(20.0, $result->gainPercentage);
		// 365 days → 20*(365/365) = 20.0
		self::assertSame(20.0, $result->gainPercentagePerAnnum);
		self::assertSame(20.0, $result->return->toFloat());
		self::assertSame(20.0, $result->returnPercentage);
		self::assertSame(20.0, $result->returnPercentagePerAnnum);
	}

	public function testCalculateTwoAssets730Days(): void
	{
		// 2022-01-01 to 2024-01-01 = 730 days (neither 2022 nor 2023 is a leap year)
		$dateTime = new DateTimeImmutable('2024-01-01');
		$firstTransaction = new DateTimeImmutable('2022-01-01');

		$asset1 = $this->makeAssetData(
			value: new Decimal(150),
			transactionValueDefaultCurrency: new Decimal(150),
			gainDefaultCurrency: new Decimal(30),
		);
		$asset2 = $this->makeAssetData(
			value: new Decimal(70),
			transactionValueDefaultCurrency: new Decimal(50),
			gainDefaultCurrency: new Decimal(20),
		);

		$result = $this->calculator->calculate([$asset1, $asset2], $dateTime, $firstTransaction);

		// sumTransactionValue = 200, sumGain = 50
		self::assertSame(220.0, $result->value->toFloat());
		self::assertSame(200.0, $result->transactionValue->toFloat());
		self::assertSame(50.0, $result->gain->toFloat());
		// 50/200*100 = 25.0
		self::assertSame(25.0, $result->gainPercentage);
		// 25/(730/365) = 12.5
		self::assertSame(12.5, $result->gainPercentagePerAnnum);
	}

	public function testCalculateWithDividendAndFxImpact(): void
	{
		// 365 days → per-annum equals percentage
		$dateTime = new DateTimeImmutable('2023-01-01');
		$firstTransaction = new DateTimeImmutable('2022-01-01');

		$asset = $this->makeAssetData(
			transactionValueDefaultCurrency: new Decimal(100),
			gainDefaultCurrency: new Decimal(10),
			dividendYieldDefaultCurrency: new Decimal(5),
			fxImpact: new Decimal(2),
		);

		$result = $this->calculator->calculate([$asset], $dateTime, $firstTransaction);

		self::assertSame(10.0, $result->gainPercentage);
		self::assertSame(10.0, $result->gainPercentagePerAnnum);
		self::assertSame(5.0, $result->dividendYieldPercentage);
		self::assertSame(5.0, $result->dividendYieldPercentagePerAnnum);
		self::assertSame(2.0, $result->fxImpactPercentage);
		self::assertSame(2.0, $result->fxImpactPercentagePerAnnum);
		// 10+5+2
		self::assertSame(17.0, $result->return->toFloat());
		self::assertSame(17.0, $result->returnPercentage);
		self::assertSame(17.0, $result->returnPercentagePerAnnum);
	}

	public function testCalculateAggregatesTaxFeeAndRealizedGain(): void
	{
		$now = new DateTimeImmutable('2024-01-01');

		$asset1 = $this->makeAssetData(
			taxDefaultCurrency: new Decimal(3),
			feeDefaultCurrency: new Decimal('1.5'),
			realizedGainDefaultCurrency: new Decimal(40),
		);
		$asset2 = $this->makeAssetData(
			taxDefaultCurrency: new Decimal(2),
			feeDefaultCurrency: new Decimal('0.5'),
			realizedGainDefaultCurrency: new Decimal(60),
		);

		$result = $this->calculator->calculate([$asset1, $asset2], $now, $now);

		self::assertSame(5.0, $result->tax->toFloat());
		self::assertSame(2.0, $result->fee->toFloat());
		self::assertSame(100.0, $result->realizedGain->toFloat());
	}

	private function makeAssetData(
		Decimal $value = new Decimal(0),
		Decimal $transactionValueDefaultCurrency = new Decimal(0),
		Decimal $gainDefaultCurrency = new Decimal(0),
		Decimal $realizedGainDefaultCurrency = new Decimal(0),
		Decimal $dividendYieldDefaultCurrency = new Decimal(0),
		Decimal $fxImpact = new Decimal(0),
		Decimal $taxDefaultCurrency = new Decimal(0),
		Decimal $feeDefaultCurrency = new Decimal(0),
	): AssetDataDto {
		$zero = new Decimal(0);

		return new AssetDataDto(
			date: new DateTimeImmutable(),
			price: $zero,
			units: $zero,
			value: $value,
			transactionValue: $zero,
			transactionValueDefaultCurrency: $transactionValueDefaultCurrency,
			averagePrice: $zero,
			averagePriceDefaultCurrency: $zero,
			gain: $zero,
			gainDefaultCurrency: $gainDefaultCurrency,
			realizedGain: $zero,
			realizedGainDefaultCurrency: $realizedGainDefaultCurrency,
			gainPercentage: 0.0,
			gainPercentagePerAnnum: 0.0,
			dividendYield: $zero,
			dividendYieldDefaultCurrency: $dividendYieldDefaultCurrency,
			dividendYieldPercentage: 0.0,
			dividendYieldPercentagePerAnnum: 0.0,
			fxImpact: $fxImpact,
			fxImpactPercentage: 0.0,
			fxImpactPercentagePerAnnum: 0.0,
			return: $zero,
			returnPercentage: 0.0,
			returnPercentagePerAnnum: 0.0,
			tax: $zero,
			taxDefaultCurrency: $taxDefaultCurrency,
			fee: $zero,
			feeDefaultCurrency: $feeDefaultCurrency,
			firstTransactionActionCreated: new DateTimeImmutable(),
		);
	}
}
