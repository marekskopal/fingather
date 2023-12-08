<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Service\Provider\PortfolioProvider;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PortfolioController
{
	public function __construct(private readonly PortfolioProvider $portfolioProvider,)
	{
	}

	public function actionGetPortfolio(ServerRequestInterface $request): ResponseInterface
	{
		return new JsonResponse(['test' => 'test']);
	}
}
