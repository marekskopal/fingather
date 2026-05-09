<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Response\NotFoundResponse;
use FinGather\Route\Routes;
use FinGather\Service\DataCalculator\TaxOptimizationCalculator;
use FinGather\Service\Provider\PortfolioProviderInterface;
use FinGather\Service\Request\RequestServiceInterface;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteGet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class TaxOptimizationController
{
	public function __construct(
		private TaxOptimizationCalculator $taxOptimizationCalculator,
		private PortfolioProviderInterface $portfolioProvider,
		private RequestServiceInterface $requestService,
	) {
	}

	#[RouteGet(Routes::TaxOptimization->value)]
	public function actionGetTaxOptimization(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		return new JsonResponse($this->taxOptimizationCalculator->calculate($user, $portfolio));
	}
}
