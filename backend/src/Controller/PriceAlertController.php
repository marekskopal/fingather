<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\PriceAlertCreateDto;
use FinGather\Dto\PriceAlertDto;
use FinGather\Dto\PriceAlertUpdateDto;
use FinGather\Model\Entity\PriceAlert;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Provider\PriceAlertProvider;
use FinGather\Service\Provider\TickerProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteDelete;
use MarekSkopal\Router\Attribute\RouteGet;
use MarekSkopal\Router\Attribute\RoutePost;
use MarekSkopal\Router\Attribute\RoutePut;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class PriceAlertController
{
	public function __construct(
		private PriceAlertProvider $priceAlertProvider,
		private PortfolioProvider $portfolioProvider,
		private TickerProvider $tickerProvider,
		private RequestService $requestService,
	) {
	}

	#[RouteGet(Routes::PriceAlerts->value)]
	public function actionGetPriceAlerts(ServerRequestInterface $request): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		$priceAlerts = array_map(
			fn (PriceAlert $priceAlert): PriceAlertDto => PriceAlertDto::fromEntity($priceAlert),
			iterator_to_array($this->priceAlertProvider->getPriceAlerts($user), false),
		);

		return new JsonResponse($priceAlerts);
	}

	#[RouteGet(Routes::PriceAlert->value)]
	public function actionGetPriceAlert(ServerRequestInterface $request, int $priceAlertId): ResponseInterface
	{
		if ($priceAlertId < 1) {
			return new NotFoundResponse('Price alert id is required.');
		}

		$priceAlert = $this->priceAlertProvider->getPriceAlert(
			priceAlertId: $priceAlertId,
			user: $this->requestService->getUser($request),
		);
		if ($priceAlert === null) {
			return new NotFoundResponse('Price alert with id "' . $priceAlertId . '" was not found.');
		}

		return new JsonResponse(PriceAlertDto::fromEntity($priceAlert));
	}

	#[RoutePost(Routes::PriceAlerts->value)]
	public function actionPostPriceAlert(ServerRequestInterface $request): ResponseInterface
	{
		$user = $this->requestService->getUser($request);
		$dto = $this->requestService->getRequestBodyDto($request, PriceAlertCreateDto::class);

		$portfolio = null;
		if ($dto->portfolioId !== null) {
			$portfolio = $this->portfolioProvider->getPortfolio($user, $dto->portfolioId);
			if ($portfolio === null) {
				return new NotFoundResponse('Portfolio with id "' . $dto->portfolioId . '" was not found.');
			}
		}

		$ticker = null;
		if ($dto->tickerId !== null) {
			$ticker = $this->tickerProvider->getTicker($dto->tickerId);
			if ($ticker === null) {
				return new NotFoundResponse('Ticker with id "' . $dto->tickerId . '" was not found.');
			}
		}

		return new JsonResponse(PriceAlertDto::fromEntity($this->priceAlertProvider->createPriceAlert(
			user: $user,
			type: $dto->type,
			condition: $dto->condition,
			targetValue: $dto->targetValue,
			recurrence: $dto->recurrence,
			cooldownHours: $dto->cooldownHours,
			portfolio: $portfolio,
			ticker: $ticker,
		)));
	}

	#[RoutePut(Routes::PriceAlert->value)]
	public function actionPutPriceAlert(ServerRequestInterface $request, int $priceAlertId): ResponseInterface
	{
		if ($priceAlertId < 1) {
			return new NotFoundResponse('Price alert id is required.');
		}

		$user = $this->requestService->getUser($request);

		$priceAlert = $this->priceAlertProvider->getPriceAlert(priceAlertId: $priceAlertId, user: $user);
		if ($priceAlert === null) {
			return new NotFoundResponse('Price alert with id "' . $priceAlertId . '" was not found.');
		}

		$dto = $this->requestService->getRequestBodyDto($request, PriceAlertUpdateDto::class);

		$portfolio = null;
		if ($dto->portfolioId !== null) {
			$portfolio = $this->portfolioProvider->getPortfolio($user, $dto->portfolioId);
			if ($portfolio === null) {
				return new NotFoundResponse('Portfolio with id "' . $dto->portfolioId . '" was not found.');
			}
		}

		$ticker = null;
		if ($dto->tickerId !== null) {
			$ticker = $this->tickerProvider->getTicker($dto->tickerId);
			if ($ticker === null) {
				return new NotFoundResponse('Ticker with id "' . $dto->tickerId . '" was not found.');
			}
		}

		return new JsonResponse(PriceAlertDto::fromEntity($this->priceAlertProvider->updatePriceAlert(
			priceAlert: $priceAlert,
			type: $dto->type,
			condition: $dto->condition,
			targetValue: $dto->targetValue,
			recurrence: $dto->recurrence,
			cooldownHours: $dto->cooldownHours,
			isActive: $dto->isActive,
			portfolio: $portfolio,
			ticker: $ticker,
		)));
	}

	#[RouteDelete(Routes::PriceAlert->value)]
	public function actionDeletePriceAlert(ServerRequestInterface $request, int $priceAlertId): ResponseInterface
	{
		if ($priceAlertId < 1) {
			return new NotFoundResponse('Price alert id is required.');
		}

		$priceAlert = $this->priceAlertProvider->getPriceAlert(
			priceAlertId: $priceAlertId,
			user: $this->requestService->getUser($request),
		);
		if ($priceAlert === null) {
			return new NotFoundResponse('Price alert with id "' . $priceAlertId . '" was not found.');
		}

		$this->priceAlertProvider->deletePriceAlert($priceAlert);

		return new OkResponse();
	}
}
