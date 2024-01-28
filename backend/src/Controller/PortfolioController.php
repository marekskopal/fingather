<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\PortfolioDto;
use FinGather\Model\Entity\Portfolio;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PortfolioController
{
	public function __construct(private readonly PortfolioProvider $portfolioProvider, private readonly RequestService $requestService,)
	{
	}

	public function actionGetPortfolios(ServerRequestInterface $request): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		$portfolios = array_map(
			fn (Portfolio $portfolio): PortfolioDto => PortfolioDto::fromEntity($portfolio),
			iterator_to_array($this->portfolioProvider->getPortfolios($user)),
		);

		return new JsonResponse($portfolios);
	}

	public function actionGetDefaultPortfolio(ServerRequestInterface $request): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		return new JsonResponse(PortfolioDto::fromEntity($this->portfolioProvider->getDefaultPortfolio($user)));
	}
}
