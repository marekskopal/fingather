<?php

declare(strict_types=1);

namespace FinGather\Controller;

use DateInterval;
use DateTimeImmutable;
use FinGather\Dto\TickerDataDto;
use FinGather\Response\NotFoundResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\Dto\TickerDataAdjustedDto;
use FinGather\Service\Provider\TickerDataProviderInterface;
use FinGather\Service\Provider\TickerProvider;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteGet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class TickerDataController
{
	public function __construct(private TickerDataProviderInterface $tickerDataProvider, private TickerProvider $tickerProvider,)
	{
	}

	#[RouteGet(Routes::TickerData->value)]
	public function actionGetTickerData(ServerRequestInterface $request, int $tickerId): ResponseInterface
	{
		if ($tickerId < 1) {
			return new NotFoundResponse('Ticker id is required.');
		}

		$ticker = $this->tickerProvider->getTicker($tickerId);
		if ($ticker === null) {
			return new NotFoundResponse('Ticker with id "' . $tickerId . '" was not found.');
		}

		$toDate = new DateTimeImmutable('today');
		$fromDate = $toDate->sub(DateInterval::createFromDateString('1 year'));

		$tickerDatas = array_map(
			fn (TickerDataAdjustedDto $tickerData): TickerDataDto => TickerDataDto::fromTickerDataAdjusted($tickerData),
			$this->tickerDataProvider->getAdjustedTickerDatas($ticker, $fromDate, $toDate),
		);

		return new JsonResponse($tickerDatas);
	}
}
