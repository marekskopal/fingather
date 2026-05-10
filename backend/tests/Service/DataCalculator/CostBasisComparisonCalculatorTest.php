<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\DataCalculator;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Country;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\CostBasisMethodEnum;
use FinGather\Model\Entity\Enum\TaxJurisdictionEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\CostBasisComparisonCalculator;
use FinGather\Service\DataCalculator\Dto\CostBasisComparisonDto;
use FinGather\Service\DataCalculator\Dto\CostBasisComparisonRowDto;
use FinGather\Service\DataCalculator\Dto\TaxReportRealizedGainsDto;
use FinGather\Service\DataCalculator\TaxReportRealizedGainsCalculatorInterface;
use FinGather\Service\Tax\Jurisdiction\CzechRepublicTaxJurisdictionRules;
use FinGather\Service\Tax\Jurisdiction\GenericTaxJurisdictionRules;
use FinGather\Service\Tax\Jurisdiction\GermanyTaxJurisdictionRules;
use FinGather\Service\Tax\Jurisdiction\SlovakiaTaxJurisdictionRules;
use FinGather\Service\Tax\Jurisdiction\TaxJurisdictionRulesFactory;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CostBasisComparisonCalculator::class)]
#[UsesClass(Country::class)]
#[UsesClass(Currency::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(User::class)]
#[UsesClass(CostBasisComparisonDto::class)]
#[UsesClass(CostBasisComparisonRowDto::class)]
#[UsesClass(TaxReportRealizedGainsDto::class)]
#[UsesClass(CzechRepublicTaxJurisdictionRules::class)]
#[UsesClass(SlovakiaTaxJurisdictionRules::class)]
#[UsesClass(GermanyTaxJurisdictionRules::class)]
#[UsesClass(GenericTaxJurisdictionRules::class)]
#[UsesClass(TaxJurisdictionRulesFactory::class)]
final class CostBasisComparisonCalculatorTest extends TestCase
{
	public function testProducesOneRowPerMethod(): void
	{
		$result = $this->runComparison(
			czech: true,
			configuredMethod: CostBasisMethodEnum::Fifo,
			netByMethod: ['Fifo' => '500', 'Lifo' => '300', 'AverageCost' => '400'],
		);

		self::assertCount(3, $result->rows);
		$methods = array_map(fn(CostBasisComparisonRowDto $row): string => $row->method->value, $result->rows);
		self::assertSame(['Fifo', 'Lifo', 'AverageCost'], $methods);
	}

	public function testFlagsLifoAsNotAllowedForCzech(): void
	{
		$result = $this->runComparison(
			czech: true,
			configuredMethod: CostBasisMethodEnum::Fifo,
			netByMethod: ['Fifo' => '500', 'Lifo' => '300', 'AverageCost' => '400'],
		);

		$rowsByMethod = $this->indexByMethod($result->rows);
		self::assertTrue($rowsByMethod['Fifo']->allowedInJurisdiction);
		self::assertFalse($rowsByMethod['Lifo']->allowedInJurisdiction);
		self::assertTrue($rowsByMethod['AverageCost']->allowedInJurisdiction);
	}

	public function testGenericAllowsAllMethods(): void
	{
		$result = $this->runComparison(
			czech: false,
			configuredMethod: CostBasisMethodEnum::Fifo,
			netByMethod: ['Fifo' => '500', 'Lifo' => '300', 'AverageCost' => '400'],
		);

		foreach ($result->rows as $row) {
			self::assertTrue($row->allowedInJurisdiction);
		}
	}

	public function testOptimalMethodIsSmallestNetGain(): void
	{
		$result = $this->runComparison(
			czech: true,
			configuredMethod: CostBasisMethodEnum::Fifo,
			netByMethod: ['Fifo' => '500', 'Lifo' => '300', 'AverageCost' => '400'],
		);

		// LIFO has the smallest net gain → optimal regardless of legality flag
		self::assertSame(CostBasisMethodEnum::Lifo, $result->optimalMethod);
		self::assertSame(CostBasisMethodEnum::Fifo, $result->configuredMethod);
	}

	public function testEstimatedTaxAndDeltaWithRate(): void
	{
		$result = $this->runComparison(
			czech: true,
			configuredMethod: CostBasisMethodEnum::Fifo,
			netByMethod: ['Fifo' => '1000', 'Lifo' => '600', 'AverageCost' => '800'],
		);

		$rowsByMethod = $this->indexByMethod($result->rows);
		// At 0.15 rate: Fifo tax = 150, Lifo tax = 90 → savings 60
		$fifoTax = $rowsByMethod['Fifo']->estimatedTax;
		$lifoTax = $rowsByMethod['Lifo']->estimatedTax;
		self::assertNotNull($fifoTax);
		self::assertNotNull($lifoTax);
		self::assertSame(150.0, $fifoTax->toFloat());
		self::assertSame(90.0, $lifoTax->toFloat());
		// deltaVsConfigured for Fifo (configured) is 0; for Lifo it's +60 saving vs Fifo
		$fifoDelta = $rowsByMethod['Fifo']->deltaVsConfigured;
		$lifoDelta = $rowsByMethod['Lifo']->deltaVsConfigured;
		self::assertNotNull($fifoDelta);
		self::assertNotNull($lifoDelta);
		self::assertSame(0.0, $fifoDelta->toFloat());
		self::assertSame(60.0, $lifoDelta->toFloat());
	}

	public function testCurrentYearUsesNowAsYearEnd(): void
	{
		$currentYear = (int) (new DateTimeImmutable())->format('Y');
		$capturedYearEnds = [];

		$realizedCalculator = self::createStub(TaxReportRealizedGainsCalculatorInterface::class);
		$realizedCalculator->method('calculate')
			->willReturnCallback(static function (
				User $_user,
				Portfolio $_portfolio,
				DateTimeImmutable $_start,
				DateTimeImmutable $end,
				CostBasisMethodEnum $method,
			) use (&$capturedYearEnds): TaxReportRealizedGainsDto {
				$capturedYearEnds[] = $end;
				return new TaxReportRealizedGainsDto(
					method: $method,
					totalSalesProceeds: new Decimal(0),
					totalCostBasis: new Decimal(0),
					totalGains: new Decimal(0),
					totalLosses: new Decimal(0),
					totalFees: new Decimal(0),
					netRealizedGainLoss: new Decimal(0),
					transactions: [],
				);
			});

		$factory = new TaxJurisdictionRulesFactory(
			new CzechRepublicTaxJurisdictionRules(),
			new SlovakiaTaxJurisdictionRules(),
			new GermanyTaxJurisdictionRules(),
			new GenericTaxJurisdictionRules(),
		);

		$portfolio = PortfolioFixture::getPortfolio();
		$portfolio->taxJurisdiction = TaxJurisdictionEnum::Generic;
		$portfolio->costBasisMethod = CostBasisMethodEnum::Fifo;

		$calculator = new CostBasisComparisonCalculator($realizedCalculator, $factory);
		$calculator->calculate(UserFixture::getUser(), $portfolio, $currentYear);

		// One yearEnd per method (3 methods); all should be at most "now", not Dec 31.
		$decemberCutoff = new DateTimeImmutable($currentYear . '-12-31 00:00:00');
		self::assertNotEmpty($capturedYearEnds);
		foreach ($capturedYearEnds as $yearEnd) {
			self::assertLessThan($decemberCutoff, $yearEnd);
		}
	}

	public function testPastYearUsesDecember31AsYearEnd(): void
	{
		$capturedYearEnds = [];

		$realizedCalculator = self::createStub(TaxReportRealizedGainsCalculatorInterface::class);
		$realizedCalculator->method('calculate')
			->willReturnCallback(static function (
				User $_user,
				Portfolio $_portfolio,
				DateTimeImmutable $_start,
				DateTimeImmutable $end,
				CostBasisMethodEnum $method,
			) use (&$capturedYearEnds): TaxReportRealizedGainsDto {
				$capturedYearEnds[] = $end;
				return new TaxReportRealizedGainsDto(
					method: $method,
					totalSalesProceeds: new Decimal(0),
					totalCostBasis: new Decimal(0),
					totalGains: new Decimal(0),
					totalLosses: new Decimal(0),
					totalFees: new Decimal(0),
					netRealizedGainLoss: new Decimal(0),
					transactions: [],
				);
			});

		$factory = new TaxJurisdictionRulesFactory(
			new CzechRepublicTaxJurisdictionRules(),
			new SlovakiaTaxJurisdictionRules(),
			new GermanyTaxJurisdictionRules(),
			new GenericTaxJurisdictionRules(),
		);

		$portfolio = PortfolioFixture::getPortfolio();
		$portfolio->taxJurisdiction = TaxJurisdictionEnum::Generic;
		$portfolio->costBasisMethod = CostBasisMethodEnum::Fifo;

		$calculator = new CostBasisComparisonCalculator($realizedCalculator, $factory);
		$calculator->calculate(UserFixture::getUser(), $portfolio, 2024);

		$expected = new DateTimeImmutable('2024-12-31 23:59:59');
		self::assertNotEmpty($capturedYearEnds);
		foreach ($capturedYearEnds as $yearEnd) {
			self::assertEquals($expected, $yearEnd);
		}
	}

	public function testNegativeNetClampsToZeroTax(): void
	{
		$result = $this->runComparison(
			czech: true,
			configuredMethod: CostBasisMethodEnum::Fifo,
			netByMethod: ['Fifo' => '-200', 'Lifo' => '-500', 'AverageCost' => '-100'],
		);

		// Realized losses → no positive taxable amount → zero tax for all rows
		foreach ($result->rows as $row) {
			self::assertNotNull($row->estimatedTax);
			self::assertSame(0.0, $row->estimatedTax->toFloat());
		}
		// Optimal is the most negative (smallest net) — Lifo
		self::assertSame(CostBasisMethodEnum::Lifo, $result->optimalMethod);
	}

	/** @param array<string, string> $netByMethod */
	private function runComparison(
		bool $czech,
		CostBasisMethodEnum $configuredMethod,
		array $netByMethod,
		string $totalSalesProceeds = '200000',
	): CostBasisComparisonDto {
		$realizedCalculator = self::createStub(TaxReportRealizedGainsCalculatorInterface::class);
		$realizedCalculator->method('calculate')
			->willReturnCallback(static function (
				User $_user,
				Portfolio $_portfolio,
				DateTimeImmutable $_start,
				DateTimeImmutable $_end,
				CostBasisMethodEnum $method,
			) use (
				$netByMethod,
				$totalSalesProceeds
): TaxReportRealizedGainsDto {
				$net = new Decimal($netByMethod[$method->value]);
				return new TaxReportRealizedGainsDto(
					method: $method,
					totalSalesProceeds: new Decimal($totalSalesProceeds),
					totalCostBasis: new Decimal('9500'),
					totalGains: $net->isPositive() ? $net : new Decimal(0),
					totalLosses: $net->isNegative() ? $net->abs() : new Decimal(0),
					totalFees: new Decimal(0),
					netRealizedGainLoss: $net,
					transactions: [],
				);
			});

		$factory = new TaxJurisdictionRulesFactory(
			new CzechRepublicTaxJurisdictionRules(),
			new SlovakiaTaxJurisdictionRules(),
			new GermanyTaxJurisdictionRules(),
			new GenericTaxJurisdictionRules(),
		);

		$portfolio = PortfolioFixture::getPortfolio();
		$portfolio->costBasisMethod = $configuredMethod;
		if ($czech) {
			$portfolio->taxJurisdiction = TaxJurisdictionEnum::CzechRepublic;
			$portfolio->estimatedTaxRate = new Decimal('0.15');
		} else {
			$portfolio->taxJurisdiction = TaxJurisdictionEnum::Generic;
			$portfolio->estimatedTaxRate = null;
		}

		$calculator = new CostBasisComparisonCalculator($realizedCalculator, $factory);
		return $calculator->calculate(UserFixture::getUser(), $portfolio, 2024);
	}

	public function testCzechGrossProceedsExemptionZeroesOutTax(): void
	{
		// Proceeds below the 100k CZK threshold → all rows tax-free regardless of net gain.
		$result = $this->runComparison(
			czech: true,
			configuredMethod: CostBasisMethodEnum::Fifo,
			netByMethod: ['Fifo' => '5000', 'Lifo' => '4000', 'AverageCost' => '4500'],
			totalSalesProceeds: '80000',
		);

		foreach ($result->rows as $row) {
			self::assertNotNull($row->estimatedTax);
			self::assertSame(0.0, $row->estimatedTax->toFloat());
		}
		self::assertNotNull($result->annualGrossProceedsExemption);
		self::assertSame(100000.0, $result->annualGrossProceedsExemption->toFloat());
	}

	public function testGermanyAllowanceReducesTax(): void
	{
		$realizedCalculator = self::createStub(TaxReportRealizedGainsCalculatorInterface::class);
		$realizedCalculator->method('calculate')
			->willReturnCallback(static function (
				User $_user,
				Portfolio $_portfolio,
				DateTimeImmutable $_start,
				DateTimeImmutable $_end,
				CostBasisMethodEnum $method,
			): TaxReportRealizedGainsDto {
				return new TaxReportRealizedGainsDto(
					method: $method,
					totalSalesProceeds: new Decimal('20000'),
					totalCostBasis: new Decimal('15000'),
					totalGains: new Decimal('5000'),
					totalLosses: new Decimal(0),
					totalFees: new Decimal(0),
					netRealizedGainLoss: new Decimal('5000'),
					transactions: [],
				);
			});

		$factory = new TaxJurisdictionRulesFactory(
			new CzechRepublicTaxJurisdictionRules(),
			new SlovakiaTaxJurisdictionRules(),
			new GermanyTaxJurisdictionRules(),
			new GenericTaxJurisdictionRules(),
		);

		$portfolio = PortfolioFixture::getPortfolio();
		$portfolio->taxJurisdiction = TaxJurisdictionEnum::Germany;
		$portfolio->costBasisMethod = CostBasisMethodEnum::Fifo;
		$portfolio->estimatedTaxRate = new Decimal('0.26375');

		$calculator = new CostBasisComparisonCalculator($realizedCalculator, $factory);
		$result = $calculator->calculate(UserFixture::getUser(), $portfolio, 2024);

		// (5000 - 1000) * 0.26375 = 1055
		$rowsByMethod = $this->indexByMethod($result->rows);
		self::assertNotNull($rowsByMethod['Fifo']->estimatedTax);
		self::assertSame(1055.0, $rowsByMethod['Fifo']->estimatedTax->toFloat());
		self::assertNotNull($result->annualGainExemption);
		self::assertSame(1000.0, $result->annualGainExemption->toFloat());
	}

	/**
	 * @param list<CostBasisComparisonRowDto> $rows
	 * @return array<string, CostBasisComparisonRowDto>
	 */
	private function indexByMethod(array $rows): array
	{
		$out = [];
		foreach ($rows as $row) {
			$out[$row->method->value] = $row;
		}
		return $out;
	}
}
