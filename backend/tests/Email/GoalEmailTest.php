<?php

declare(strict_types=1);

namespace FinGather\Tests\Email;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Email\GoalEmail;
use FinGather\Model\Entity\Enum\GoalTypeEnum;
use FinGather\Tests\Fixtures\Model\Entity\GoalFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(GoalEmail::class)]
final class GoalEmailTest extends TestCase
{
	/** @return array<string, mixed> */
	private function makeTranslations(): array
	{
		return [
			'portfolioValueGoal' => 'Portfolio Value Goal',
			'returnPercentageGoal' => 'Return % Goal',
			'investedAmountGoal' => 'Invested Amount Goal',
			'achieved' => 'Achieved',
			'reached' => 'Your portfolio has reached',
			'targetPortfolioValue' => 'Target Portfolio Value',
			'targetReturn' => 'Target Return',
			'targetInvested' => 'Target Invested',
			'currentPortfolioValue' => 'Current Portfolio Value',
			'currentReturn' => 'Current Return',
			'currentInvested' => 'Current Invested',
			'deadline' => 'Deadline',
			'auto' => 'This is an automated message.',
		];
	}

	public function testGetHtmlContainsPortfolioName(): void
	{
		$portfolio = PortfolioFixture::getPortfolio(name: 'My Portfolio');
		$goal = GoalFixture::getGoal(portfolio: $portfolio);

		$html = GoalEmail::getHtml($goal, '1500.00', $this->makeTranslations());

		self::assertStringContainsString('My Portfolio', $html);
	}

	public function testGetHtmlContainsCurrentValue(): void
	{
		$goal = GoalFixture::getGoal();

		$html = GoalEmail::getHtml($goal, '9876.54', $this->makeTranslations());

		self::assertStringContainsString('9,876.54', $html);
	}

	public function testGetHtmlContainsTargetValue(): void
	{
		$goal = GoalFixture::getGoal(targetValue: new Decimal('5000'));

		$html = GoalEmail::getHtml($goal, '5000.00', $this->makeTranslations());

		self::assertStringContainsString('5,000.00', $html);
	}

	public function testGetHtmlShowsDeadlineRowWhenPresent(): void
	{
		$goal = GoalFixture::getGoal(deadline: new DateTimeImmutable('2025-12-31'));

		$html = GoalEmail::getHtml($goal, '1000.00', $this->makeTranslations());

		self::assertStringContainsString('2025-12-31', $html);
		self::assertStringContainsString('Deadline', $html);
	}

	public function testGetHtmlHidesDeadlineRowWhenNull(): void
	{
		$goal = GoalFixture::getGoal(deadline: null);

		$html = GoalEmail::getHtml($goal, '1000.00', $this->makeTranslations());

		self::assertStringNotContainsString('Deadline', $html);
	}

	/** @return array<string, array{GoalTypeEnum, string}> */
	public static function goalTypeProvider(): array
	{
		return [
			'portfolio value' => [GoalTypeEnum::PortfolioValue, 'Portfolio Value Goal'],
			'return percentage' => [GoalTypeEnum::ReturnPercentage, 'Return % Goal'],
			'invested amount' => [GoalTypeEnum::InvestedAmount, 'Invested Amount Goal'],
		];
	}

	#[DataProvider('goalTypeProvider')]
	public function testGetHtmlContainsCorrectGoalTypeLabel(GoalTypeEnum $type, string $expectedLabel): void
	{
		$goal = GoalFixture::getGoal(type: $type);

		$html = GoalEmail::getHtml($goal, '1000.00', $this->makeTranslations());

		self::assertStringContainsString($expectedLabel, $html);
	}

	public function testGetHtmlReturnPercentageAppendsSuffix(): void
	{
		$goal = GoalFixture::getGoal(type: GoalTypeEnum::ReturnPercentage, targetValue: new Decimal('15'));

		$html = GoalEmail::getHtml($goal, '20.5', $this->makeTranslations());

		self::assertStringContainsString('20.50 %', $html);
	}

	public function testGetHtmlIsValidHtml(): void
	{
		$goal = GoalFixture::getGoal();

		$html = GoalEmail::getHtml($goal, '1000.00', $this->makeTranslations());

		self::assertStringContainsString('<html>', $html);
		self::assertStringContainsString('</html>', $html);
		self::assertStringContainsString('<body', $html);
		self::assertStringContainsString('</body>', $html);
	}
}
