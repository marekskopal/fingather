<?php

declare(strict_types=1);

namespace FinGather\Tests\Controller;

use ArrayIterator;
use Decimal\Decimal;
use FinGather\Controller\GoalController;
use FinGather\Model\Entity\Goal;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Service\Goal\GoalCheckerInterface;
use FinGather\Service\Provider\GoalProviderInterface;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Request\RequestServiceInterface;
use FinGather\Tests\Fixtures\Model\Entity\GoalFixture;
use FinGather\Tests\Fixtures\Model\Entity\PortfolioFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

#[CoversClass(GoalController::class)]
#[UsesClass(Goal::class)]
#[UsesClass(Portfolio::class)]
#[UsesClass(User::class)]
#[UsesClass(NotFoundResponse::class)]
#[UsesClass(OkResponse::class)]
final class GoalControllerTest extends TestCase
{
	private GoalProviderInterface&Stub $goalProvider;

	private PortfolioProvider&Stub $portfolioProvider;

	private GoalCheckerInterface&Stub $goalChecker;

	private RequestServiceInterface&Stub $requestService;

	private GoalController $goalController;

	protected function setUp(): void
	{
		$this->goalProvider = $this::createStub(GoalProviderInterface::class);
		$this->portfolioProvider = $this::createStub(PortfolioProvider::class);
		$this->goalChecker = $this::createStub(GoalCheckerInterface::class);
		$this->requestService = $this::createStub(RequestServiceInterface::class);
		$this->requestService->method('getUser')->willReturn(UserFixture::getUser());
		$this->goalChecker->method('getCurrentValue')->willReturn(new Decimal('5000'));
		$this->goalChecker->method('getProgressPercentage')->willReturn(50.0);

		$this->goalController = new GoalController(
			$this->goalProvider,
			$this->portfolioProvider,
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
}
