<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\PortfolioDataDto;
use FinGather\Service\Provider\PortfolioDataProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Safe\DateTimeImmutable;

class PortfolioDataController
{
	public function __construct(
		private readonly PortfolioDataProvider $portfolioDataProvider,
		private readonly RequestService $requestService
	)
	{
	}

	public function actionGetPortfolioData(ServerRequestInterface $request): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		$dateTime = new DateTimeImmutable();

		$portfolioData = $this->portfolioDataProvider->getPortfolioData($user, $dateTime);

		return new JsonResponse(PortfolioDataDto::fromEntity($portfolioData));
	}
}
