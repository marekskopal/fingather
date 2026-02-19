<?php

declare(strict_types=1);

namespace FinGather\Controller;

use DateTimeImmutable;
use FinGather\Dto\GoalCreateDto;
use FinGather\Dto\GoalDto;
use FinGather\Dto\GoalUpdateDto;
use FinGather\Model\Entity\Goal;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Route\Routes;
use FinGather\Service\Goal\GoalChecker;
use FinGather\Service\Provider\GoalProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteDelete;
use MarekSkopal\Router\Attribute\RouteGet;
use MarekSkopal\Router\Attribute\RoutePost;
use MarekSkopal\Router\Attribute\RoutePut;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class GoalController
{
	public function __construct(
		private GoalProvider $goalProvider,
		private PortfolioProvider $portfolioProvider,
		private GoalChecker $goalChecker,
		private RequestService $requestService,
	) {
	}

	#[RouteGet(Routes::Goals->value)]
	public function actionGetGoals(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		$now = new DateTimeImmutable();

		$goals = array_map(
			function (Goal $goal) use ($now): GoalDto {
				$currentValue = $this->goalChecker->getCurrentValue($goal, $now);
				$progressPercentage = $this->goalChecker->getProgressPercentage($goal, $currentValue);
				return GoalDto::fromEntity($goal, $currentValue, $progressPercentage);
			},
			iterator_to_array($this->goalProvider->getGoals($user, $portfolio), false),
		);

		return new JsonResponse($goals);
	}

	#[RouteGet(Routes::Goal->value)]
	public function actionGetGoal(ServerRequestInterface $request, int $goalId): ResponseInterface
	{
		if ($goalId < 1) {
			return new NotFoundResponse('Goal id is required.');
		}

		$user = $this->requestService->getUser($request);

		$goal = $this->goalProvider->getGoal(goalId: $goalId, user: $user);
		if ($goal === null) {
			return new NotFoundResponse('Goal with id "' . $goalId . '" was not found.');
		}

		$now = new DateTimeImmutable();
		$currentValue = $this->goalChecker->getCurrentValue($goal, $now);
		$progressPercentage = $this->goalChecker->getProgressPercentage($goal, $currentValue);

		return new JsonResponse(GoalDto::fromEntity($goal, $currentValue, $progressPercentage));
	}

	#[RoutePost(Routes::Goals->value)]
	public function actionPostGoal(ServerRequestInterface $request): ResponseInterface
	{
		$user = $this->requestService->getUser($request);
		$dto = $this->requestService->getRequestBodyDto($request, GoalCreateDto::class);

		$portfolio = $this->portfolioProvider->getPortfolio($user, $dto->portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $dto->portfolioId . '" was not found.');
		}

		$goal = $this->goalProvider->createGoal(
			user: $user,
			portfolio: $portfolio,
			type: $dto->type,
			targetValue: $dto->targetValue,
			deadline: $dto->deadline,
		);

		$now = new DateTimeImmutable();
		$currentValue = $this->goalChecker->getCurrentValue($goal, $now);
		$progressPercentage = $this->goalChecker->getProgressPercentage($goal, $currentValue);

		return new JsonResponse(GoalDto::fromEntity($goal, $currentValue, $progressPercentage));
	}

	#[RoutePut(Routes::Goal->value)]
	public function actionPutGoal(ServerRequestInterface $request, int $goalId): ResponseInterface
	{
		if ($goalId < 1) {
			return new NotFoundResponse('Goal id is required.');
		}

		$user = $this->requestService->getUser($request);

		$goal = $this->goalProvider->getGoal(goalId: $goalId, user: $user);
		if ($goal === null) {
			return new NotFoundResponse('Goal with id "' . $goalId . '" was not found.');
		}

		$dto = $this->requestService->getRequestBodyDto($request, GoalUpdateDto::class);

		$portfolio = $this->portfolioProvider->getPortfolio($user, $dto->portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $dto->portfolioId . '" was not found.');
		}

		$goal = $this->goalProvider->updateGoal(
			goal: $goal,
			portfolio: $portfolio,
			type: $dto->type,
			targetValue: $dto->targetValue,
			deadline: $dto->deadline,
			isActive: $dto->isActive,
		);

		$now = new DateTimeImmutable();
		$currentValue = $this->goalChecker->getCurrentValue($goal, $now);
		$progressPercentage = $this->goalChecker->getProgressPercentage($goal, $currentValue);

		return new JsonResponse(GoalDto::fromEntity($goal, $currentValue, $progressPercentage));
	}

	#[RouteDelete(Routes::Goal->value)]
	public function actionDeleteGoal(ServerRequestInterface $request, int $goalId): ResponseInterface
	{
		if ($goalId < 1) {
			return new NotFoundResponse('Goal id is required.');
		}

		$goal = $this->goalProvider->getGoal(
			goalId: $goalId,
			user: $this->requestService->getUser($request),
		);
		if ($goal === null) {
			return new NotFoundResponse('Goal with id "' . $goalId . '" was not found.');
		}

		$this->goalProvider->deleteGoal($goal);

		return new OkResponse();
	}
}
