<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\DataCalculator;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Dto\PortfolioDataDto;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use FinGather\Service\DataCalculator\Dto\YearCalculatedDataDto;
use FinGather\Service\DataCalculator\OverviewDataCalculator;
use FinGather\Service\Provider\PortfolioDataProviderInterface;
use FinGather\Service\Provider\TransactionProviderInterface;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\TransactionFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use FinGather\Utils\CalculatorUtils;
use FinGather\Utils\DateTimeUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OverviewDataCalculator::class)]
#[UsesClass(YearCalculatedDataDto::class)]
#[UsesClass(PortfolioDataDto::class)]
#[UsesClass(CalculatedDataDto::class)]
#[UsesClass(CalculatorUtils::class)]
#[UsesClass(DateTimeUtils::class)]
final class OverviewDataCalculatorTest extends TestCase
{
	public function testReturnsEmptyWhenNoFirstTransaction(): void
	{
		$transactionProvider = self::createStub(TransactionProviderInterface::class);
		$transactionProvider->method('getFirstTransaction')->willReturn(null);

		$calculator = new OverviewDataCalculator($transactionProvider, self::createStub(PortfolioDataProviderInterface::class));

		$result = $calculator->yearCalculate(UserFixture::getUser(), PortfolioFixture::getPortfolio());

		self::assertSame([], $result);
	}

	public function testSingleYearHasNullInterannualValues(): void
	{
		// First transaction in the current year → only one iteration in the loop
		$currentYear = (int) (new DateTimeImmutable('today'))->format('Y');

		$firstTransaction = TransactionFixture::getTransaction(
			actionCreated: new DateTimeImmutable($currentYear . '-01-01'),
		);

		$transactionProvider = self::createStub(TransactionProviderInterface::class);
		$transactionProvider->method('getFirstTransaction')->willReturn($firstTransaction);

		$portfolioDataProvider = self::createStub(PortfolioDataProviderInterface::class);
		$portfolioDataProvider->method('getPortfolioData')->willReturn($this->makeCalculatedData(value: new Decimal('1000')));

		$calculator = new OverviewDataCalculator($transactionProvider, $portfolioDataProvider);

		$result = $calculator->yearCalculate(UserFixture::getUser(), PortfolioFixture::getPortfolio());

		self::assertArrayHasKey($currentYear, $result);
		self::assertNull($result[$currentYear]->valueInterannually);
		self::assertNull($result[$currentYear]->gainInterannually);
		self::assertNull($result[$currentYear]->dividendYieldInterannually);
		self::assertNull($result[$currentYear]->returnInterannually);
	}

	public function testYearsAreKeyedByYear(): void
	{
		$currentYear = (int) (new DateTimeImmutable('today'))->format('Y');

		$firstTransaction = TransactionFixture::getTransaction(
			actionCreated: new DateTimeImmutable($currentYear . '-06-01'),
		);

		$transactionProvider = self::createStub(TransactionProviderInterface::class);
		$transactionProvider->method('getFirstTransaction')->willReturn($firstTransaction);

		$portfolioDataProvider = self::createStub(PortfolioDataProviderInterface::class);
		$portfolioDataProvider->method('getPortfolioData')->willReturn($this->makeCalculatedData());

		$calculator = new OverviewDataCalculator($transactionProvider, $portfolioDataProvider);

		$result = $calculator->yearCalculate(UserFixture::getUser(), PortfolioFixture::getPortfolio());

		self::assertArrayHasKey($currentYear, $result);
		self::assertSame($currentYear, $result[$currentYear]->year);
	}

	public function testSecondYearHasInterannualDifferences(): void
	{
		// First transaction in the previous year → two iterations in the loop
		$today = new DateTimeImmutable('today');
		$currentYear = (int) $today->format('Y');
		$previousYear = $currentYear - 1;

		$firstTransaction = TransactionFixture::getTransaction(
			actionCreated: new DateTimeImmutable($previousYear . '-01-01'),
		);

		$transactionProvider = self::createStub(TransactionProviderInterface::class);
		$transactionProvider->method('getFirstTransaction')->willReturn($firstTransaction);

		$dataPreviousYear = $this->makeCalculatedData(value: new Decimal('1000'), gain: new Decimal('100'));
		$dataCurrentYear = $this->makeCalculatedData(value: new Decimal('1500'), gain: new Decimal('200'));

		$portfolioDataProvider = self::createStub(PortfolioDataProviderInterface::class);
		$portfolioDataProvider->method('getPortfolioData')
			->willReturnCallback(
				static function (mixed $user, mixed $portfolio, DateTimeImmutable $date) use ($dataPreviousYear, $dataCurrentYear, $currentYear): CalculatedDataDto {
					return $date->format('Y') === (string) $currentYear ? $dataCurrentYear : $dataPreviousYear;
				},
			);

		$calculator = new OverviewDataCalculator($transactionProvider, $portfolioDataProvider);

		$result = $calculator->yearCalculate(UserFixture::getUser(), PortfolioFixture::getPortfolio());

		self::assertArrayHasKey($previousYear, $result);
		self::assertArrayHasKey($currentYear, $result);

		// Previous year: first year, no interannual
		self::assertNull($result[$previousYear]->valueInterannually);

		// Current year: interannual = currentYear - previousYear
		self::assertSame(1500.0, $result[$currentYear]->value->toFloat());
		self::assertSame(500.0, $result[$currentYear]->valueInterannually?->toFloat());
		self::assertSame(100.0, $result[$currentYear]->gainInterannually?->toFloat()); // 200 - 100
	}

	private function makeCalculatedData(
		Decimal $value = new Decimal('0'),
		Decimal $gain = new Decimal('0'),
	): CalculatedDataDto {
		$zero = new Decimal(0);

		return new CalculatedDataDto(
			date: new DateTimeImmutable(),
			value: $value,
			transactionValue: $zero,
			gain: $gain,
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
}
