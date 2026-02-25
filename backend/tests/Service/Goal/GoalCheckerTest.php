<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Goal;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Enum\GoalTypeEnum;
use FinGather\Model\Entity\Goal;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use FinGather\Service\Goal\GoalChecker;
use FinGather\Service\Provider\PortfolioDataProvider;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GoalChecker::class)]
#[UsesClass(Goal::class)]
#[UsesClass(CalculatedDataDto::class)]
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
		);

		$checker = $this->makeChecker($portfolioData);
		$value = $checker->getCurrentValue($goal, new DateTimeImmutable());

		self::assertSame(15.5, $value->toFloat());
	}

	private function makeChecker(?CalculatedDataDto $portfolioData = null): GoalChecker
	{
		$portfolioDataProvider = self::createStub(PortfolioDataProvider::class);

		if ($portfolioData !== null) {
			$portfolioDataProvider->method('getPortfolioData')
				->willReturn($portfolioData);
		}

		return new GoalChecker($portfolioDataProvider);
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
