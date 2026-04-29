<?php

declare(strict_types=1);

namespace FinGather\Tests\Controller;

use ArrayIterator;
use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Controller\GoalController;
use FinGather\Dto\GoalCreateDto;
use FinGather\Dto\GoalDto;
use FinGather\Dto\GoalReachabilityDto;
use FinGather\Dto\GoalUpdateDto;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\DcaPlan;
use FinGather\Model\Entity\Enum\GoalTypeEnum;
use FinGather\Model\Entity\Goal;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Service\Goal\GoalCheckerInterface;
use FinGather\Service\Provider\DcaPlanProviderInterface;
use FinGather\Service\Provider\GoalProviderInterface;
use FinGather\Service\Provider\PortfolioProviderInterface;
use FinGather\Service\Request\RequestServiceInterface;
use FinGather\Tests\Fixtures\Model\Entity\DcaPlanFixture;
use FinGather\Tests\Fixtures\Model\Entity\GoalFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use FinGather\Utils\DateTimeUtils;
use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

#[CoversClass(GoalController::class)]
#[UsesClass(Goal::class)]
#[UsesClass(GoalCreateDto::class)]
#[UsesClass(GoalUpdateDto::class)]
#[UsesClass(DcaPlan::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(User::class)]
#[UsesClass(NotFoundResponse::class)]
#[UsesClass(OkResponse::class)]
#[UsesClass(GoalReachabilityDto::class)]
#[UsesClass(Currency::class)]
#[UsesClass(DateTimeUtils::class)]
#[UsesClass(GoalDto::class)]
final class GoalControllerTest extends TestCase
{
	private GoalProviderInterface&Stub $goalProvider;

	private PortfolioProviderInterface&Stub $portfolioProvider;

	private DcaPlanProviderInterface&Stub $dcaPlanProvider;

	private GoalCheckerInterface&Stub $goalChecker;

	private RequestServiceInterface&Stub $requestService;

	private GoalController $goalController;

	protected function setUp(): void
	{
		$this->goalProvider = $this::createStub(GoalProviderInterface::class);
		$this->portfolioProvider = $this::createStub(PortfolioProviderInterface::class);
		$this->dcaPlanProvider = $this::createStub(DcaPlanProviderInterface::class);
		$this->goalChecker = $this::createStub(GoalCheckerInterface::class);
		$this->requestService = $this::createStub(RequestServiceInterface::class);
		$this->requestService->method('getUser')->willReturn(UserFixture::getUser());
		$this->goalChecker->method('getCurrentValue')->willReturn(new Decimal('5000'));
		$this->goalChecker->method('getProgressPercentage')->willReturn(50.0);
		$this->goalChecker->method('getReachability')->willReturn(
			new GoalReachabilityDto(isReachable: null, projectedAchievementDate: null),
		);

		$this->goalController = new GoalController(
			$this->goalProvider,
			$this->portfolioProvider,
			$this->dcaPlanProvider,
			$this->goalChecker,
			$this->requestService,
		);
	}

	public function testGetGoalsPortfolioNotFoundReturnsNotFound(): void
	{
		$this->portfolioProvider->method('getPortfolio')->willReturn(null);

		$response = $this->goalController->actionGetGoals(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testGetGoalsReturnsJsonResponse(): void
	{
		$this->portfolioProvider->method('getPortfolio')->willReturn(PortfolioFixture::getPortfolio());
		$this->goalProvider->method('getGoals')->willReturn(new ArrayIterator([]));

		$response = $this->goalController->actionGetGoals(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	#[TestWith([0])]
	#[TestWith([-1])]
	public function testGetGoalInvalidIdReturnsNotFound(int $goalId): void
	{
		$response = $this->goalController->actionGetGoal(
			$this::createStub(ServerRequestInterface::class),
			$goalId,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testGetGoalNotFoundReturnsNotFound(): void
	{
		$this->goalProvider->method('getGoal')->willReturn(null);

		$response = $this->goalController->actionGetGoal(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testGetGoalReturnsJsonResponse(): void
	{
		$goal = GoalFixture::getGoal();
		$this->goalProvider->method('getGoal')->willReturn($goal);

		$response = $this->goalController->actionGetGoal(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	#[TestWith([0])]
	#[TestWith([-1])]
	public function testPutGoalInvalidIdReturnsNotFound(int $goalId): void
	{
		$response = $this->goalController->actionPutGoal(
			$this::createStub(ServerRequestInterface::class),
			$goalId,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testPutGoalNotFoundReturnsNotFound(): void
	{
		$this->goalProvider->method('getGoal')->willReturn(null);

		$response = $this->goalController->actionPutGoal(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	#[TestWith([0])]
	#[TestWith([-1])]
	public function testDeleteGoalInvalidIdReturnsNotFound(int $goalId): void
	{
		$response = $this->goalController->actionDeleteGoal(
			$this::createStub(ServerRequestInterface::class),
			$goalId,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testDeleteGoalNotFoundReturnsNotFound(): void
	{
		$this->goalProvider->method('getGoal')->willReturn(null);

		$response = $this->goalController->actionDeleteGoal(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testDeleteGoalReturnsOkResponse(): void
	{
		$goal = GoalFixture::getGoal();
		$this->goalProvider->method('getGoal')->willReturn($goal);

		$response = $this->goalController->actionDeleteGoal(
			$this::createStub(ServerRequestInterface::class),
			1,
		);

		self::assertInstanceOf(OkResponse::class, $response);
	}

	// --- actionPostGoal happy paths ---

	public function testPostGoalInvalidPortfolioIdReturnsNotFound(): void
	{
		$response = $this->goalController->actionPostGoal($this::createStub(ServerRequestInterface::class), 0);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testPostGoalPortfolioNotFoundReturnsNotFound(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn($this->makeCreateDto());
		$this->portfolioProvider->method('getPortfolio')->willReturn(null);

		$response = $this->goalController->actionPostGoal($this::createStub(ServerRequestInterface::class), 1);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testPostGoalReturnsJsonResponseOnSuccess(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn($this->makeCreateDto());
		$this->portfolioProvider->method('getPortfolio')->willReturn(PortfolioFixture::getPortfolio());
		$this->goalProvider->method('createGoal')->willReturn(GoalFixture::getGoal());

		$response = $this->goalController->actionPostGoal($this::createStub(ServerRequestInterface::class), 1);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	public function testPostGoalWithDcaPlanLooksUpAndPasses(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn($this->makeCreateDto(dcaPlanId: 5));
		$this->portfolioProvider->method('getPortfolio')->willReturn(PortfolioFixture::getPortfolio());
		$this->dcaPlanProvider->method('getDcaPlan')->willReturn(DcaPlanFixture::getDcaPlan());
		$this->goalProvider->method('createGoal')->willReturn(GoalFixture::getGoal());

		$response = $this->goalController->actionPostGoal($this::createStub(ServerRequestInterface::class), 1);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	// --- actionPutGoal happy paths ---

	public function testPutGoalPortfolioNotFoundReturnsNotFound(): void
	{
		$this->goalProvider->method('getGoal')->willReturn(GoalFixture::getGoal());
		$this->requestService->method('getRequestBodyDto')->willReturn($this->makeUpdateDto());
		$this->portfolioProvider->method('getPortfolio')->willReturn(null);

		$response = $this->goalController->actionPutGoal($this::createStub(ServerRequestInterface::class), 1);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testPutGoalReturnsJsonResponseOnSuccess(): void
	{
		$goal = GoalFixture::getGoal();
		$this->goalProvider->method('getGoal')->willReturn($goal);
		$this->requestService->method('getRequestBodyDto')->willReturn($this->makeUpdateDto());
		$this->portfolioProvider->method('getPortfolio')->willReturn(PortfolioFixture::getPortfolio());
		$this->goalProvider->method('updateGoal')->willReturn($goal);

		$response = $this->goalController->actionPutGoal($this::createStub(ServerRequestInterface::class), 1);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	private function makeCreateDto(?int $dcaPlanId = null): GoalCreateDto
	{
		return new GoalCreateDto(
			portfolioId: 1,
			type: GoalTypeEnum::PortfolioValue,
			targetValue: new Decimal('10000'),
			deadline: new DateTimeImmutable('2030-01-01'),
			dcaPlanId: $dcaPlanId,
		);
	}

	private function makeUpdateDto(?int $dcaPlanId = null): GoalUpdateDto
	{
		return new GoalUpdateDto(
			portfolioId: 1,
			type: GoalTypeEnum::PortfolioValue,
			targetValue: new Decimal('10000'),
			deadline: new DateTimeImmutable('2030-01-01'),
			isActive: true,
			dcaPlanId: $dcaPlanId,
		);
	}
}
