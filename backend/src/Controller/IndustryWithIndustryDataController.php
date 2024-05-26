<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Response\NotFoundResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\IndustryWithIndustryDataProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Request\RequestServiceInterface;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteGet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Safe\DateTimeImmutable;

final class IndustryWithIndustryDataController
{
	public function __construct(
		private readonly IndustryWithIndustryDataProvider $industryWithIndustryDataProvider,
		private readonly PortfolioProvider $portfolioProvider,
		private readonly RequestServiceInterface $requestService,
	) {
	}

	#[RouteGet(Routes::IndustriesWithIndustryData->value)]
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

		return new JsonResponse($this->industryWithIndustryDataProvider->getIndustriesWithIndustryData($user, $portfolio, $dateTime));
	}
}
