<?php

declare(strict_types=1);

namespace FinGather\Controller;

use DateTimeImmutable;
use FinGather\Response\NotFoundResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\StrategyComparisonProvider;
use FinGather\Service\Provider\StrategyProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteGet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class StrategyWithComparisonController
{
	public function __construct(
		private StrategyComparisonProvider $strategyComparisonProvider,
		private StrategyProvider $strategyProvider,
		private RequestService $requestService,
	) {
	}

	#[RouteGet(Routes::StrategyWithComparison->value)]
	public function actionGetStrategyWithComparison(ServerRequestInterface $request, int $strategyId): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		if ($strategyId < 1) {
			return new NotFoundResponse('Strategy id is required.');
		}

		$strategy = $this->strategyProvider->getStrategy($user, $strategyId);
		if ($strategy === null) {
			return new NotFoundResponse('Strategy with id "' . $strategyId . '" was not found.');
		}

		$portfolio = $strategy->portfolio;
		$dateTime = new DateTimeImmutable();

		return new JsonResponse($this->strategyComparisonProvider->getStrategyWithComparison(
			$user,
			$portfolio,
			$strategy,
			$dateTime,
		));
	}
}
