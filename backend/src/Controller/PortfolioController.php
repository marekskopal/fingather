<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\PortfolioCreateDto;
use FinGather\Dto\PortfolioDto;
use FinGather\Dto\PortfolioUpdateDto;
use FinGather\Model\Entity\Portfolio;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\CurrencyProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteDelete;
use MarekSkopal\Router\Attribute\RouteGet;
use MarekSkopal\Router\Attribute\RoutePost;
use MarekSkopal\Router\Attribute\RoutePut;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class PortfolioController
{
	public function __construct(
		private readonly PortfolioProvider $portfolioProvider,
		private readonly CurrencyProvider $currencyProvider,
		private readonly RequestService $requestService,
	)
	{
	}

	#[RouteGet(Routes::Portfolios->value)]
	public function actionGetPortfolios(ServerRequestInterface $request): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		$portfolios = array_map(
			fn (Portfolio $portfolio): PortfolioDto => PortfolioDto::fromEntity($portfolio),
			iterator_to_array($this->portfolioProvider->getPortfolios($user), false),
		);

		return new JsonResponse($portfolios);
	}

	#[RouteGet(Routes::Portfolio->value)]
	public function actionGetPortfolio(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio(
			user: $this->requestService->getUser($request),
			portfolioId: $portfolioId,
		);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		return new JsonResponse(PortfolioDto::fromEntity($portfolio));
	}

	#[RouteGet(Routes::PortfolioDefault->value)]
	public function actionGetDefaultPortfolio(ServerRequestInterface $request): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		return new JsonResponse(PortfolioDto::fromEntity($this->portfolioProvider->getDefaultPortfolio($user)));
	}

	#[RoutePost(Routes::Portfolios->value)]
	public function actionPostPortfolio(ServerRequestInterface $request): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		$portfolioCreateDto = $this->requestService->getRequestBodyDto($request, PortfolioCreateDto::class);

		$currency = $this->currencyProvider->getCurrency($portfolioCreateDto->currencyId);
		if ($currency === null) {
			return new NotFoundResponse('Currency with id "' . $portfolioCreateDto->currencyId . '" was not found.');
		}

		return new JsonResponse(PortfolioDto::fromEntity($this->portfolioProvider->createPortfolio(
			user: $user,
			currency: $currency,
			name: $portfolioCreateDto->name,
			isDefault: $portfolioCreateDto->isDefault,
		)));
	}

	#[RoutePut(Routes::Portfolio->value)]
	public function actionPutPortfolio(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio(
			user: $this->requestService->getUser($request),
			portfolioId: $portfolioId,
		);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		$portfolioUpdateDto = $this->requestService->getRequestBodyDto($request, PortfolioUpdateDto::class);

		if ($portfolioUpdateDto->currencyId === null) {
			$currency = $portfolio->getCurrency();
		} else {
			$currency = $this->currencyProvider->getCurrency($portfolioUpdateDto->currencyId);
			if ($currency === null) {
				return new NotFoundResponse('Currency with id "' . $portfolioUpdateDto->currencyId . '" was not found.');
			}
		}

		return new JsonResponse(PortfolioDto::fromEntity($this->portfolioProvider->updatePortfolio(
			portfolio: $portfolio,
			currency: $currency,
			name: $portfolioUpdateDto->name ?? $portfolio->getName(),
			isDefault: $portfolioUpdateDto->isDefault ?? true,
		)));
	}

	#[RouteDelete(Routes::Portfolio->value)]
	public function actionDeletePortfolio(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio(
			user: $this->requestService->getUser($request),
			portfolioId: $portfolioId,
		);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		$this->portfolioProvider->deletePortfolio($portfolio);

		return new OkResponse();
	}
}
