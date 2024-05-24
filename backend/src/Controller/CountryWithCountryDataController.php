<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Response\NotFoundResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\CountryWithCountryDataProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Request\RequestServiceInterface;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteGet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Safe\DateTimeImmutable;

final class CountryWithCountryDataController
{
	public function __construct(
		private readonly CountryWithCountryDataProvider $countryWithCountryDataProvider,
		private readonly PortfolioProvider $portfolioProvider,
		private readonly RequestServiceInterface $requestService,
	) {
	}

	#[RouteGet(Routes::CountriesWithCountryData->value)]
	public function actionGetCountriesWithCountryData(ServerRequestInterface $request, int $portfolioId): ResponseInterface
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

		return new JsonResponse($this->countryWithCountryDataProvider->getCountriesWithCountryData($user, $portfolio, $dateTime));
	}
}
