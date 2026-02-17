<?php

declare(strict_types=1);

namespace FinGather\Controller;

use DateTimeImmutable;
use FinGather\Response\NotFoundResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Provider\SectorWithSectorDataProvider;
use FinGather\Service\Request\RequestServiceInterface;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteGet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class SectorWithSectorDataController
{
	public function __construct(
		private readonly SectorWithSectorDataProvider $sectorWithSectorDataProvider,
		private readonly PortfolioProvider $portfolioProvider,
		private readonly RequestServiceInterface $requestService,
	) {
	}

	#[RouteGet(Routes::SectorsWithSectorData->value)]
	public function actionGetTickerSectorsWithTickerSectorData(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		$dateTime = new DateTimeImmutable();

		return new JsonResponse($this->sectorWithSectorDataProvider->getSectorsWithSectorData($user, $portfolio, $dateTime));
	}
}
