<?php

declare(strict_types=1);

namespace FinGather\Tests\Service;

use ArrayIterator;
use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Country;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\AlertConditionEnum;
use FinGather\Model\Entity\Enum\AlertRecurrenceEnum;
use FinGather\Model\Entity\Enum\PriceAlertTypeEnum;
use FinGather\Model\Entity\Industry;
use FinGather\Model\Entity\Market;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\PriceAlert;
use FinGather\Model\Entity\Sector;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use FinGather\Service\PriceAlert\PriceAlertChecker;
use FinGather\Service\Provider\PortfolioDataProviderInterface;
use FinGather\Service\Provider\PortfolioProviderInterface;
use FinGather\Service\Provider\PriceAlertProviderInterface;
use FinGather\Service\Provider\TickerDataProviderInterface;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\PriceAlertFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PriceAlertChecker::class)]
#[UsesClass(PriceAlert::class)]
#[UsesClass(CalculatedDataDto::class)]
#[UsesClass(Currency::class)]
#[UsesClass(Industry::class)]
#[UsesClass(Market::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(Sector::class)]
#[UsesClass(Ticker::class)]
#[UsesClass(User::class)]
#[UsesClass(Country::class)]
final class PriceAlertCheckerTest extends TestCase
{
	// --- checkAlerts ---

	public function testCheckAlertsReturnsEmptyWhenNoAlerts(): void
	{
		$checker = $this->makeChecker(alerts: []);

		self::assertSame([], $checker->checkAlerts());
	}

	public function testSkipsAlertOnActiveCooldown(): void
	{
		$alert = PriceAlertFixture::getPriceAlert(cooldownHours: 24);
		$alert->lastTriggeredAt = new DateTimeImmutable('-1 hour');

		$checker = $this->makeChecker(alerts: [$alert]);

		self::assertSame([], $checker->checkAlerts());
	}

	public function testDoesNotSkipWhenNoLastTriggeredAt(): void
	{
		$alert = PriceAlertFixture::getPriceAlert(
			type: PriceAlertTypeEnum::Price,
			condition: AlertConditionEnum::Above,
			targetValue: new Decimal('100'),
		);
		$alert->lastTriggeredAt = null;

		$tickerDataProvider = self::createStub(TickerDataProviderInterface::class);
		$tickerDataProvider->method('getLastTickerDataClose')->willReturn(new Decimal('150'));

		$checker = $this->makeChecker(alerts: [$alert], tickerDataProvider: $tickerDataProvider);

		self::assertCount(1, $checker->checkAlerts());
	}

	public function testDoesNotSkipWhenCooldownExpired(): void
	{
		$alert = PriceAlertFixture::getPriceAlert(
			type: PriceAlertTypeEnum::Price,
			condition: AlertConditionEnum::Above,
			targetValue: new Decimal('100'),
			cooldownHours: 1,
		);
		$alert->lastTriggeredAt = new DateTimeImmutable('-2 hours');

		$tickerDataProvider = self::createStub(TickerDataProviderInterface::class);
		$tickerDataProvider->method('getLastTickerDataClose')->willReturn(new Decimal('150'));

		$checker = $this->makeChecker(alerts: [$alert], tickerDataProvider: $tickerDataProvider);

		self::assertCount(1, $checker->checkAlerts());
	}

	public function testPriceAlertAboveTriggered(): void
	{
		$alert = PriceAlertFixture::getPriceAlert(
			type: PriceAlertTypeEnum::Price,
			condition: AlertConditionEnum::Above,
			targetValue: new Decimal('200'),
		);

		$tickerDataProvider = self::createStub(TickerDataProviderInterface::class);
		$tickerDataProvider->method('getLastTickerDataClose')->willReturn(new Decimal('250'));

		$checker = $this->makeChecker(alerts: [$alert], tickerDataProvider: $tickerDataProvider);
		$triggered = $checker->checkAlerts();

		self::assertCount(1, $triggered);
		self::assertSame('250.00', $triggered[0]['currentValue']);
	}

	public function testPriceAlertAboveNotTriggeredWhenBelowTarget(): void
	{
		$alert = PriceAlertFixture::getPriceAlert(
			type: PriceAlertTypeEnum::Price,
			condition: AlertConditionEnum::Above,
			targetValue: new Decimal('200'),
		);

		$tickerDataProvider = self::createStub(TickerDataProviderInterface::class);
		$tickerDataProvider->method('getLastTickerDataClose')->willReturn(new Decimal('150'));

		$checker = $this->makeChecker(alerts: [$alert], tickerDataProvider: $tickerDataProvider);

		self::assertSame([], $checker->checkAlerts());
	}

	public function testPriceAlertBelowTriggered(): void
	{
		$alert = PriceAlertFixture::getPriceAlert(
			type: PriceAlertTypeEnum::Price,
			condition: AlertConditionEnum::Below,
			targetValue: new Decimal('200'),
		);

		$tickerDataProvider = self::createStub(TickerDataProviderInterface::class);
		$tickerDataProvider->method('getLastTickerDataClose')->willReturn(new Decimal('150'));

		$checker = $this->makeChecker(alerts: [$alert], tickerDataProvider: $tickerDataProvider);
		$triggered = $checker->checkAlerts();

		self::assertCount(1, $triggered);
		self::assertSame('150.00', $triggered[0]['currentValue']);
	}

	public function testPriceAlertBelowNotTriggeredWhenAboveTarget(): void
	{
		$alert = PriceAlertFixture::getPriceAlert(
			type: PriceAlertTypeEnum::Price,
			condition: AlertConditionEnum::Below,
			targetValue: new Decimal('200'),
		);

		$tickerDataProvider = self::createStub(TickerDataProviderInterface::class);
		$tickerDataProvider->method('getLastTickerDataClose')->willReturn(new Decimal('250'));

		$checker = $this->makeChecker(alerts: [$alert], tickerDataProvider: $tickerDataProvider);

		self::assertSame([], $checker->checkAlerts());
	}

	public function testPriceAlertSkipsWhenNoTickerData(): void
	{
		$alert = PriceAlertFixture::getPriceAlert(type: PriceAlertTypeEnum::Price);

		$tickerDataProvider = self::createStub(TickerDataProviderInterface::class);
		$tickerDataProvider->method('getLastTickerDataClose')->willReturn(null);

		$checker = $this->makeChecker(alerts: [$alert], tickerDataProvider: $tickerDataProvider);

		self::assertSame([], $checker->checkAlerts());
	}

	public function testPortfolioAlertAboveTriggered(): void
	{
		$alert = PriceAlertFixture::getPriceAlert(
			type: PriceAlertTypeEnum::Portfolio,
			condition: AlertConditionEnum::Above,
			targetValue: new Decimal('10'),
		);

		$portfolioDataProvider = self::createStub(PortfolioDataProviderInterface::class);
		$portfolioDataProvider->method('getPortfolioData')->willReturn($this->makePortfolioData(gainPercentage: 15.0));

		$checker = $this->makeChecker(alerts: [$alert], portfolioDataProvider: $portfolioDataProvider);
		$triggered = $checker->checkAlerts();

		self::assertCount(1, $triggered);
		self::assertSame('15.00', $triggered[0]['currentValue']);
	}

	public function testPortfolioAlertAboveNotTriggeredWhenBelowTarget(): void
	{
		$alert = PriceAlertFixture::getPriceAlert(
			type: PriceAlertTypeEnum::Portfolio,
			condition: AlertConditionEnum::Above,
			targetValue: new Decimal('10'),
		);

		$portfolioDataProvider = self::createStub(PortfolioDataProviderInterface::class);
		$portfolioDataProvider->method('getPortfolioData')->willReturn($this->makePortfolioData(gainPercentage: 5.0));

		$checker = $this->makeChecker(alerts: [$alert], portfolioDataProvider: $portfolioDataProvider);

		self::assertSame([], $checker->checkAlerts());
	}

	public function testPortfolioAlertBelowTriggered(): void
	{
		$alert = PriceAlertFixture::getPriceAlert(
			type: PriceAlertTypeEnum::Portfolio,
			condition: AlertConditionEnum::Below,
			targetValue: new Decimal('5'),
		);

		$portfolioDataProvider = self::createStub(PortfolioDataProviderInterface::class);
		$portfolioDataProvider->method('getPortfolioData')->willReturn($this->makePortfolioData(gainPercentage: 2.0));

		$checker = $this->makeChecker(alerts: [$alert], portfolioDataProvider: $portfolioDataProvider);
		$triggered = $checker->checkAlerts();

		self::assertCount(1, $triggered);
		self::assertSame('2.00', $triggered[0]['currentValue']);
	}

	public function testPortfolioAlertUsesDefaultPortfolioWhenAlertHasNone(): void
	{
		// PriceAlertFixture defaults portfolio to null
		$alert = PriceAlertFixture::getPriceAlert(
			type: PriceAlertTypeEnum::Portfolio,
			condition: AlertConditionEnum::Above,
			targetValue: new Decimal('5'),
		);

		$defaultPortfolio = PortfolioFixture::getPortfolio();

		$portfolioProvider = self::createMock(PortfolioProviderInterface::class);
		$portfolioProvider->expects(self::once())
			->method('getDefaultPortfolio')
			->willReturn($defaultPortfolio);

		$portfolioDataProvider = self::createStub(PortfolioDataProviderInterface::class);
		$portfolioDataProvider->method('getPortfolioData')->willReturn($this->makePortfolioData(gainPercentage: 10.0));

		$checker = $this->makeChecker(
			alerts: [$alert],
			portfolioDataProvider: $portfolioDataProvider,
			portfolioProvider: $portfolioProvider,
		);
		$checker->checkAlerts();
	}

	// --- markTriggered ---

	public function testMarkTriggeredSetsLastTriggeredAt(): void
	{
		$alert = PriceAlertFixture::getPriceAlert(recurrence: AlertRecurrenceEnum::Recurring);

		$this->makeChecker()->markTriggered($alert);

		self::assertNotNull($alert->lastTriggeredAt);
	}

	public function testMarkTriggeredDeactivatesOneTimeAlert(): void
	{
		$alert = PriceAlertFixture::getPriceAlert(recurrence: AlertRecurrenceEnum::OneTime);

		$this->makeChecker()->markTriggered($alert);

		self::assertFalse($alert->isActive);
	}

	public function testMarkTriggeredKeepsRecurringAlertActive(): void
	{
		$alert = PriceAlertFixture::getPriceAlert(recurrence: AlertRecurrenceEnum::Recurring);

		$this->makeChecker()->markTriggered($alert);

		self::assertTrue($alert->isActive);
	}

	// --- Helpers ---

	/** @param list<PriceAlert> $alerts */
	private function makeChecker(
		array $alerts = [],
		?TickerDataProviderInterface $tickerDataProvider = null,
		?PortfolioDataProviderInterface $portfolioDataProvider = null,
		?PortfolioProviderInterface $portfolioProvider = null,
	): PriceAlertChecker {
		$priceAlertProvider = self::createStub(PriceAlertProviderInterface::class);
		$priceAlertProvider->method('getActivePriceAlerts')->willReturn(new ArrayIterator($alerts));

		if ($portfolioProvider === null) {
			$portfolioProvider = self::createStub(PortfolioProviderInterface::class);
			$portfolioProvider->method('getDefaultPortfolio')->willReturn(PortfolioFixture::getPortfolio());
		}

		return new PriceAlertChecker(
			priceAlertProvider: $priceAlertProvider,
			tickerDataProvider: $tickerDataProvider ?? self::createStub(TickerDataProviderInterface::class),
			portfolioDataProvider: $portfolioDataProvider ?? self::createStub(PortfolioDataProviderInterface::class),
			portfolioProvider: $portfolioProvider,
		);
	}

	private function makePortfolioData(float $gainPercentage = 0.0): CalculatedDataDto
	{
		$zero = new Decimal(0);

		return new CalculatedDataDto(
			date: new DateTimeImmutable(),
			value: $zero,
			transactionValue: $zero,
			gain: $zero,
			gainPercentage: $gainPercentage,
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
