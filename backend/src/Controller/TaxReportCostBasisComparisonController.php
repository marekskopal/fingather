<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Response\NotFoundResponse;
use FinGather\Route\Routes;
use FinGather\Service\DataCalculator\CostBasisComparisonCalculator;
use FinGather\Service\Provider\PortfolioProviderInterface;
use FinGather\Service\Request\RequestServiceInterface;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteGet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class TaxReportCostBasisComparisonController
{
	public function __construct(
		private CostBasisComparisonCalculator $costBasisComparisonCalculator,
		private PortfolioProviderInterface $portfolioProvider,
		private RequestServiceInterface $requestService,
	) {
	}

	#[RouteGet(Routes::TaxReportCostBasisComparison->value)]
	public function actionGetCostBasisComparison(ServerRequestInterface $request, int $portfolioId, int $year): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		return new JsonResponse($this->costBasisComparisonCalculator->calculate($user, $portfolio, $year));
	}
}
