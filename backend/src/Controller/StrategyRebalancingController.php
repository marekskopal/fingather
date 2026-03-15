<?php

declare(strict_types=1);

namespace FinGather\Controller;

use DateTimeImmutable;
use FinGather\Dto\StrategyRebalancingRequestDto;
use FinGather\Response\NotFoundResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\StrategyProvider;
use FinGather\Service\Provider\StrategyRebalancingProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RoutePost;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class StrategyRebalancingController
{
	public function __construct(
		private StrategyRebalancingProvider $strategyRebalancingProvider,
		private StrategyProvider $strategyProvider,
		private RequestService $requestService,
	) {
	}

	#[RoutePost(Routes::StrategyRebalancing->value)]
	public function actionPostStrategyRebalancing(ServerRequestInterface $request, int $strategyId): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		if ($strategyId < 1) {
			return new NotFoundResponse('Strategy id is required.');
		}

		$strategy = $this->strategyProvider->getStrategy($user, $strategyId);
		if ($strategy === null) {
			return new NotFoundResponse('Strategy with id "' . $strategyId . '" was not found.');
		}

		$dto = $this->requestService->getRequestBodyDto($request, StrategyRebalancingRequestDto::class);

		return new JsonResponse($this->strategyRebalancingProvider->getStrategyRebalancing(
			user: $user,
			portfolio: $strategy->portfolio,
			strategy: $strategy,
			dateTime: new DateTimeImmutable(),
			cashToInvest: $dto->cashToInvest,
			cashCurrencyId: $dto->cashCurrencyId,
			allowSelling: $dto->allowSelling,
		));
	}
}
