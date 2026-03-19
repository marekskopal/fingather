<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Response\NotFoundResponse;
use FinGather\Route\Routes;
use FinGather\Service\DataCalculator\TaxReportCalculator;
use FinGather\Service\Provider\PortfolioProviderInterface;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteGet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class TaxReportController
{
	public function __construct(
		private TaxReportCalculator $taxReportCalculator,
		private PortfolioProviderInterface $portfolioProvider,
		private RequestService $requestService,
	) {
	}

	#[RouteGet(Routes::TaxReport->value)]
	public function actionGetTaxReport(ServerRequestInterface $request, int $portfolioId, int $year): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		return new JsonResponse($this->taxReportCalculator->calculate($user, $portfolio, $year));
	}
}
