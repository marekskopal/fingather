<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\StrategyCreateDto;
use FinGather\Dto\StrategyDto;
use FinGather\Model\Entity\Strategy;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Provider\StrategyProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteDelete;
use MarekSkopal\Router\Attribute\RouteGet;
use MarekSkopal\Router\Attribute\RoutePost;
use MarekSkopal\Router\Attribute\RoutePut;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class StrategyController
{
	public function __construct(
		private readonly StrategyProvider $strategyProvider,
		private readonly PortfolioProvider $portfolioProvider,
		private readonly RequestService $requestService,
	) {
	}

	#[RouteGet(Routes::Strategies->value)]
	public function actionGetStrategies(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		$strategies = array_map(
			fn (Strategy $strategy): StrategyDto => StrategyDto::fromEntity($strategy),
			iterator_to_array($this->strategyProvider->getStrategies($user, $portfolio), false),
		);

		return new JsonResponse($strategies);
	}

	#[RouteGet(Routes::Strategy->value)]
	public function actionGetStrategy(ServerRequestInterface $request, int $strategyId): ResponseInterface
	{
		if ($strategyId < 1) {
			return new NotFoundResponse('Strategy id is required.');
		}

		$strategy = $this->strategyProvider->getStrategy(
			user: $this->requestService->getUser($request),
			strategyId: $strategyId,
		);
		if ($strategy === null) {
			return new NotFoundResponse('Strategy with id "' . $strategyId . '" was not found.');
		}

		return new JsonResponse(StrategyDto::fromEntity($strategy));
	}

	#[RouteGet(Routes::StrategyDefault->value)]
	public function actionGetDefaultStrategy(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		$strategy = $this->strategyProvider->getDefaultStrategy($user, $portfolio);
		if ($strategy === null) {
			return new NotFoundResponse('Default strategy was not found.');
		}

		return new JsonResponse(StrategyDto::fromEntity($strategy));
	}

	#[RoutePost(Routes::Strategies->value)]
	public function actionPostStrategy(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		$strategyCreateDto = $this->requestService->getRequestBodyDto($request, StrategyCreateDto::class);

		return new JsonResponse(StrategyDto::fromEntity($this->strategyProvider->createStrategy(
			user: $user,
			portfolio: $portfolio,
			name: $strategyCreateDto->name,
			isDefault: $strategyCreateDto->isDefault,
			items: $strategyCreateDto->items,
		)));
	}

	#[RoutePut(Routes::Strategy->value)]
	public function actionPutStrategy(ServerRequestInterface $request, int $strategyId): ResponseInterface
	{
		if ($strategyId < 1) {
			return new NotFoundResponse('Strategy id is required.');
		}

		$strategy = $this->strategyProvider->getStrategy(
			user: $this->requestService->getUser($request),
			strategyId: $strategyId,
		);
		if ($strategy === null) {
			return new NotFoundResponse('Strategy with id "' . $strategyId . '" was not found.');
		}

		$strategyUpdateDto = $this->requestService->getRequestBodyDto($request, StrategyCreateDto::class);

		return new JsonResponse(StrategyDto::fromEntity($this->strategyProvider->updateStrategy(
			strategy: $strategy,
			name: $strategyUpdateDto->name,
			isDefault: $strategyUpdateDto->isDefault,
			items: $strategyUpdateDto->items,
		)));
	}

	#[RouteDelete(Routes::Strategy->value)]
	public function actionDeleteStrategy(ServerRequestInterface $request, int $strategyId): ResponseInterface
	{
		if ($strategyId < 1) {
			return new NotFoundResponse('Strategy id is required.');
		}

		$strategy = $this->strategyProvider->getStrategy(
			user: $this->requestService->getUser($request),
			strategyId: $strategyId,
		);
		if ($strategy === null) {
			return new NotFoundResponse('Strategy with id "' . $strategyId . '" was not found.');
		}

		$this->strategyProvider->deleteStrategy($strategy);

		return new OkResponse();
	}
}
