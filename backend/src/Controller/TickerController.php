<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\TickerDto;
use FinGather\Model\Entity\Ticker;
use FinGather\Route\Routes;
use FinGather\Service\Provider\TickerProvider;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteGet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class TickerController
{
	public function __construct(private readonly TickerProvider $tickerProvider)
	{
	}

	#[RouteGet(Routes::Tickers->value)]
	public function actionGetTickers(ServerRequestInterface $request): ResponseInterface
	{
		/** @var array{search?: string, limit?: string, offset?: string, actionTypes?: string} $queryParams */
		$queryParams = $request->getQueryParams();

		$search = $queryParams['search'] ?? null;

		$limit = ($queryParams['limit'] ?? null) !== null ? (int) $queryParams['limit'] : null;
		$offset = ($queryParams['offset'] ?? null) !== null ? (int) $queryParams['offset'] : null;

		$tickers = array_map(
			fn (Ticker $ticker): TickerDto => TickerDto::fromEntity($ticker),
			$this->tickerProvider->getTickers(search: $search, limit: $limit, offset: $offset),
		);

		return new JsonResponse($tickers);
	}
}
