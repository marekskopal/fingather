<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\Enum\RangeEnum;
use FinGather\Response\NotFoundResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\DividendDataProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteGet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DividendDataController
{
	public function __construct(
		private readonly DividendDataProvider $dividendDataProvider,
		private readonly PortfolioProvider $portfolioProvider,
		private readonly RequestService $requestService,
	) {
	}

	#[RouteGet(Routes::DividendDataRange->value)]
	public function actionGetDividendDataRange(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		/** @var array{range?: value-of<RangeEnum>} $queryParams */
		$queryParams = $request->getQueryParams();

		$user = $this->requestService->getUser($request);

		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		$range = ($queryParams['range'] ?? null) !== null ?
			RangeEnum::from($queryParams['range']) :
			RangeEnum::All;

		return new JsonResponse($this->dividendDataProvider->getDividendData($user, $portfolio, $range));
	}
}
