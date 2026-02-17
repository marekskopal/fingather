<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\TickerFundamentalDto;
use FinGather\Response\NotFoundResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\TickerFundamentalProvider;
use FinGather\Service\Provider\TickerProvider;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteGet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class TickerFundamentalController
{
	public function __construct(
		private readonly TickerFundamentalProvider $tickerFundamentalProvider,
		private readonly TickerProvider $tickerProvider,
	)
	{
	}

	#[RouteGet(Routes::TickerFundamental->value)]
	public function actionGetTickerFundamental(ServerRequestInterface $request, int $tickerId): ResponseInterface
	{
		if ($tickerId < 1) {
			return new NotFoundResponse('Ticker id is required.');
		}

		$ticker = $this->tickerProvider->getTicker($tickerId);
		if ($ticker === null) {
			return new NotFoundResponse('Ticker with id "' . $tickerId . '" was not found.');
		}

		$tickerFundamental = $this->tickerFundamentalProvider->getTickerFundamental($ticker);
		if ($tickerFundamental === null) {
			return new NotFoundResponse('Fundamentals for ticker with id "' . $tickerId . '" was not found.');
		}

		return new JsonResponse(TickerFundamentalDto::fromEntity($tickerFundamental));
	}
}
