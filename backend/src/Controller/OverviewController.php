<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Response\NotFoundResponse;
use FinGather\Route\Routes;
use FinGather\Service\DataCalculator\OverviewDataCalculator;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteGet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class OverviewController
{
	public function __construct(
		private readonly OverviewDataCalculator $overviewDataCalculator,
		private readonly PortfolioProvider $portfolioProvider,
		private readonly RequestService $requestService,
	) {
	}

	#[RouteGet(Routes::OverviewYearOverview->value)]
	public function actionGetYearOverview(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		return new JsonResponse(array_values($this->overviewDataCalculator->yearCalculate($user, $portfolio)));
	}
}
