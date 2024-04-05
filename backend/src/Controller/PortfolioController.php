<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\PortfolioDto;
use FinGather\Model\Entity\Portfolio;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteDelete;
use MarekSkopal\Router\Attribute\RouteGet;
use MarekSkopal\Router\Attribute\RoutePost;
use MarekSkopal\Router\Attribute\RoutePut;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function Safe\json_decode;

class PortfolioController
{
	public function __construct(private readonly PortfolioProvider $portfolioProvider, private readonly RequestService $requestService,)
	{
	}

	#[RouteGet(Routes::Portfolios->value)]
	public function actionGetPortfolios(ServerRequestInterface $request): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		$portfolios = array_map(
			fn (Portfolio $portfolio): PortfolioDto => PortfolioDto::fromEntity($portfolio),
			iterator_to_array($this->portfolioProvider->getPortfolios($user)),
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

		/** @var array{name: string, isDefault: bool} $requestBody */
		$requestBody = json_decode($request->getBody()->getContents(), assoc: true);

		return new JsonResponse(PortfolioDto::fromEntity($this->portfolioProvider->createPortfolio(
			user: $user,
			name: $requestBody['name'],
			isDefault: $requestBody['isDefault'],
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

		/** @var array{name: string, isDefault: bool} $requestBody */
		$requestBody = json_decode($request->getBody()->getContents(), assoc: true);

		return new JsonResponse(PortfolioDto::fromEntity($this->portfolioProvider->updatePortfolio(
			portfolio: $portfolio,
			name: $requestBody['name'],
			isDefault: $requestBody['isDefault'],
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
