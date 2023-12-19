<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Safe\DateTimeImmutable;

class PortfolioController
{
	public function __construct(private readonly PortfolioProvider $portfolioProvider, private readonly RequestService $requestService)
	{
	}

	public function actionGetPortfolio(ServerRequestInterface $request): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		$dateTime = new DateTimeImmutable();

		$portfolio = $this->portfolioProvider->getPortfolio($user, $dateTime);

		return new JsonResponse($portfolio);
	}
}
