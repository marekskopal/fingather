<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Goal;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Dto\AssetsWithPropertiesDto;
use FinGather\Dto\DcaPlanProjectionDto;
use FinGather\Dto\DcaPlanProjectionPointDto;
use FinGather\Dto\GoalReachabilityDto;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\DcaPlan;
use FinGather\Model\Entity\Enum\GoalTypeEnum;
use FinGather\Model\Entity\Goal;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\DcaPlanDataCalculator;
use FinGather\Service\DataCalculator\DcaPlanMonteCarloSimulator;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use FinGather\Service\DataCalculator\Dto\ReturnRateDto;
use FinGather\Service\Goal\GoalChecker;
use FinGather\Service\Provider\AssetWithPropertiesProviderInterface;
use FinGather\Service\Provider\PortfolioDataProviderInterface;
use FinGather\Service\Provider\ProxyAssetProviderInterface;
use FinGather\Service\Provider\TickerDataProviderInterface;
use FinGather\Tests\Fixtures\Model\Entity\DcaPlanFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use FinGather\Utils\CalculatorUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(GoalChecker::class)]
#[UsesClass(Goal::class)]
#[UsesClass(DcaPlan::class)]
#[UsesClass(DcaPlanProjectionDto::class)]
#[UsesClass(DcaPlanProjectionPointDto::class)]
#[UsesClass(GoalReachabilityDto::class)]
#[UsesClass(DcaPlanDataCalculator::class)]
#[UsesClass(DcaPlanMonteCarloSimulator::class)]
#[UsesClass(AssetsWithPropertiesDto::class)]
#[UsesClass(ReturnRateDto::class)]
#[UsesClass(CalculatedDataDto::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(User::class)]
#[UsesClass(Currency::class)]
#[UsesClass(CalculatorUtils::class)]
final class GoalCheckerTest extends TestCase
{
	private readonly Goal $baseGoal;

	protected function setUp(): void
	{
		$this->baseGoal = new Goal(
			user: UserFixture::getUser(),
			portfolio: PortfolioFixture::getPortfolio(),
			type: GoalTypeEnum::PortfolioValue,
			targetValue: new Decimal(1000),
			deadline: null,
			isActive: true,
			achievedAt: null,
			createdAt: new DateTimeImmutable(),
			dcaPlan: null,
		);
	}

	// --- isAchieved ---

	public function testIsAchievedWhenCurrentEqualsTarget(): void
	{
		$checker = $this->makeChecker();

		self::assertTrue($checker->isAchieved($this->baseGoal, new Decimal(1000)));
	}

	public function testIsAchievedWhenCurrentExceedsTarget(): void
	{
		$checker = $this->makeChecker();

		self::assertTrue($checker->isAchieved($this->baseGoal, new Decimal(1500)));
	}

	public function testIsNotAchievedWhenCurrentBelowTarget(): void
	{
		$checker = $this->makeChecker();

		self::assertFalse($checker->isAchieved($this->baseGoal, new Decimal(999)));
	}

	// --- getProgressPercentage ---

	public function testProgressPercentageAtHalf(): void
	{
		$checker = $this->makeChecker();

		self::assertSame(50.0, $checker->getProgressPercentage($this->baseGoal, new Decimal(500)));
	}

	public function testProgressPercentageAtFull(): void
	{
		$checker = $this->makeChecker();

		self::assertSame(100.0, $checker->getProgressPercentage($this->baseGoal, new Decimal(1000)));
	}

	public function testProgressPercentageCappedAt100(): void
	{
		$checker = $this->makeChecker();

		// 1500/1000 = 150%, but capped at 100
		self::assertSame(100.0, $checker->getProgressPercentage($this->baseGoal, new Decimal(1500)));
	}

	public function testProgressPercentageWithZeroTargetReturns100(): void
	{
		$goal = new Goal(
			user: UserFixture::getUser(),
			portfolio: PortfolioFixture::getPortfolio(),
			type: GoalTypeEnum::PortfolioValue,
			targetValue: new Decimal(0),
			deadline: null,
			isActive: true,
			achievedAt: null,
			createdAt: new DateTimeImmutable(),
			dcaPlan: null,
		);

		$checker = $this->makeChecker();

		self::assertSame(100.0, $checker->getProgressPercentage($goal, new Decimal(500)));
	}

	// --- getCurrentValue ---

	public function testGetCurrentValuePortfolioValue(): void
	{
		$portfolioData = $this->makePortfolioData(value: new Decimal(2500));

		$goal = new Goal(
			user: UserFixture::getUser(),
			portfolio: PortfolioFixture::getPortfolio(),
			type: GoalTypeEnum::PortfolioValue,
			targetValue: new Decimal(3000),
			deadline: null,
			isActive: true,
			achievedAt: null,
			createdAt: new DateTimeImmutable(),
			dcaPlan: null,
		);

		$checker = $this->makeChecker($portfolioData);
		$value = $checker->getCurrentValue($goal, new DateTimeImmutable());

		self::assertSame(2500.0, $value->toFloat());
	}

	public function testGetCurrentValueInvestedAmount(): void
	{
		$portfolioData = $this->makePortfolioData(transactionValue: new Decimal(8000));

		$goal = new Goal(
			user: UserFixture::getUser(),
			portfolio: PortfolioFixture::getPortfolio(),
			type: GoalTypeEnum::InvestedAmount,
			targetValue: new Decimal(10000),
			deadline: null,
			isActive: true,
			achievedAt: null,
			createdAt: new DateTimeImmutable(),
			dcaPlan: null,
		);

		$checker = $this->makeChecker($portfolioData);
		$value = $checker->getCurrentValue($goal, new DateTimeImmutable());

		self::assertSame(8000.0, $value->toFloat());
	}

	public function testGetCurrentValueReturnPercentage(): void
	{
		$portfolioData = $this->makePortfolioData(returnPercentage: 15.5);

		$goal = new Goal(
			user: UserFixture::getUser(),
			portfolio: PortfolioFixture::getPortfolio(),
			type: GoalTypeEnum::ReturnPercentage,
			targetValue: new Decimal(20),
			deadline: null,
			isActive: true,
			achievedAt: null,
			createdAt: new DateTimeImmutable(),
			dcaPlan: null,
		);

		$checker = $this->makeChecker($portfolioData);
		$value = $checker->getCurrentValue($goal, new DateTimeImmutable());

		self::assertSame(15.5, $value->toFloat());
	}

	// --- getReachability ---

	public function testReachabilityNullWhenNoDcaPlan(): void
	{
		$goal = $this->makeGoal(GoalTypeEnum::PortfolioValue, new Decimal(10000), dcaPlan: null);

		$result = $this->makeChecker()->getReachability($goal);

		self::assertNull($result->isReachable);
		self::assertNull($result->projectedAchievementDate);
	}

	public function testReachabilityNullWhenGoalTypeIsReturnPercentage(): void
	{
		// Even with a DCA plan, ReturnPercentage goals can't be projected from contributions.
		$dcaPlan = DcaPlanFixture::getDcaPlan();
		$goal = $this->makeGoal(GoalTypeEnum::ReturnPercentage, new Decimal(15), dcaPlan: $dcaPlan);

		$result = $this->makeReachabilityChecker()->getReachability($goal);

		self::assertNull($result->isReachable);
		self::assertNull($result->projectedAchievementDate);
	}

	public function testReachabilityTrueReturnsFirstMonthAtOrAboveTarget(): void
	{
		// Linear projection (no growth, contribution 500/month from 2024-01) → target 1500 hit at month 3.
		$dcaPlan = DcaPlanFixture::getDcaPlan(amount: new Decimal('500'), startDate: new DateTimeImmutable('2024-01-01'));
		$goal = $this->makeGoal(GoalTypeEnum::PortfolioValue, new Decimal(1500), dcaPlan: $dcaPlan);

		$result = $this->makeReachabilityChecker()->getReachability($goal);

		self::assertTrue($result->isReachable);
		self::assertSame('2024-03', $result->projectedAchievementDate);
	}

	public function testReachabilityFalseWhenTargetExceedsHorizon(): void
	{
		// 50-year horizon × 500/month = 300_000 — target 1_000_000 unreachable.
		$dcaPlan = DcaPlanFixture::getDcaPlan(amount: new Decimal('500'), startDate: new DateTimeImmutable('2024-01-01'));
		$goal = $this->makeGoal(GoalTypeEnum::PortfolioValue, new Decimal(1000000), dcaPlan: $dcaPlan);

		$result = $this->makeReachabilityChecker()->getReachability($goal);

		self::assertFalse($result->isReachable);
		self::assertNull($result->projectedAchievementDate);
	}

	public function testReachabilityFalseWhenDeadlineCutsOffBeforeTargetReached(): void
	{
		// Target 1500 needs month 3 (2024-03), but deadline is 2024-02-15 → break before reach.
		$dcaPlan = DcaPlanFixture::getDcaPlan(amount: new Decimal('500'), startDate: new DateTimeImmutable('2024-01-01'));
		$goal = $this->makeGoal(
			GoalTypeEnum::PortfolioValue,
			new Decimal(1500),
			dcaPlan: $dcaPlan,
			deadline: new DateTimeImmutable('2024-02-15'),
		);

		$result = $this->makeReachabilityChecker()->getReachability($goal);

		self::assertFalse($result->isReachable);
		self::assertNull($result->projectedAchievementDate);
	}

	public function testReachabilityChecksInvestedCapitalForInvestedAmountGoal(): void
	{
		// InvestedAmount path uses point->investedCapital which equals projectedValue here.
		$dcaPlan = DcaPlanFixture::getDcaPlan(amount: new Decimal('500'), startDate: new DateTimeImmutable('2024-01-01'));
		$goal = $this->makeGoal(GoalTypeEnum::InvestedAmount, new Decimal(2000), dcaPlan: $dcaPlan);

		$result = $this->makeReachabilityChecker()->getReachability($goal);

		self::assertTrue($result->isReachable);
		// 2000 / 500 = 4 months → month 4 = 2024-04
		self::assertSame('2024-04', $result->projectedAchievementDate);
	}

	private function makeGoal(GoalTypeEnum $type, Decimal $targetValue, ?DcaPlan $dcaPlan, ?DateTimeImmutable $deadline = null,): Goal
	{
		return new Goal(
			user: UserFixture::getUser(),
			portfolio: PortfolioFixture::getPortfolio(),
			type: $type,
			targetValue: $targetValue,
			deadline: $deadline,
			isActive: true,
			achievedAt: null,
			createdAt: new DateTimeImmutable(),
			dcaPlan: $dcaPlan,
		);
	}

	private function makeChecker(?CalculatedDataDto $portfolioData = null): GoalChecker
	{
		$portfolioDataProvider = self::createStub(PortfolioDataProviderInterface::class);

		if ($portfolioData !== null) {
			$portfolioDataProvider->method('getPortfolioData')
				->willReturn($portfolioData);
		}

		$dcaPlanDataCalculator = (new ReflectionClass(DcaPlanDataCalculator::class))->newInstanceWithoutConstructor();

		return new GoalChecker($portfolioDataProvider, $dcaPlanDataCalculator);
	}

	/**
	 * Builds a checker with a real DcaPlanDataCalculator wired against stubbed dependencies that
	 * yield empty asset sets. This produces a deterministic "no growth" projection where each
	 * monthly point's investedCapital and projectedValue equal `amount * monthIndex`, letting
	 * tests assert exact reach months.
	 */
	private function makeReachabilityChecker(): GoalChecker
	{
		$portfolioDataProvider = self::createStub(PortfolioDataProviderInterface::class);

		$assetWithPropertiesProvider = self::createStub(AssetWithPropertiesProviderInterface::class);
		$assetWithPropertiesProvider->method('getAssetsWithAssetData')->willReturn(
			new AssetsWithPropertiesDto(openAssets: [], closedAssets: [], watchedAssets: []),
		);

		$tickerDataProvider = self::createStub(TickerDataProviderInterface::class);

		$dcaPlanDataCalculator = new DcaPlanDataCalculator(
			$tickerDataProvider,
			$assetWithPropertiesProvider,
			new DcaPlanMonteCarloSimulator($tickerDataProvider),
			self::createStub(ProxyAssetProviderInterface::class),
		);

		return new GoalChecker($portfolioDataProvider, $dcaPlanDataCalculator);
	}

	private function makePortfolioData(
		Decimal $value = new Decimal(0),
		Decimal $transactionValue = new Decimal(0),
		float $returnPercentage = 0.0,
	): CalculatedDataDto {
		$zero = new Decimal(0);

		return new CalculatedDataDto(
			date: new DateTimeImmutable(),
			value: $value,
			transactionValue: $transactionValue,
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
			returnPercentage: $returnPercentage,
			returnPercentagePerAnnum: 0.0,
			tax: $zero,
			fee: $zero,
		);
	}
}
