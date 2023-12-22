<?php

declare(strict_types=1);

namespace FinGather\Controller;

use DateInterval;
use FinGather\Dto\TickerDataDto;
use FinGather\Model\Entity\TickerData;
use FinGather\Response\NotFoundResponse;
use FinGather\Service\Provider\TickerDataProvider;
use FinGather\Service\Provider\TickerProvider;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Safe\DateTimeImmutable;

class TickerDataController
{
	public function __construct(private readonly TickerDataProvider $tickerDataProvider, private readonly TickerProvider $tickerProvider,)
	{
	}

	/** @param array{tickerId: string} $args */
	public function actionGetTickerData(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$tickerId = (int) $args['tickerId'];
		if ($tickerId < 1) {
			return new NotFoundResponse('Ticker id is required.');
		}

		$ticker = $this->tickerProvider->getTicker($tickerId);
		if ($ticker === null) {
			return new NotFoundResponse('Ticker with id "' . $ticker . '" was not found.');
		}

		$toDate = new DateTimeImmutable('today');
		$fromDate = $toDate->sub(DateInterval::createFromDateString('1 year'));

		$tickerDatas = array_map(
			fn (TickerData $tickerData): TickerDataDto => TickerDataDto::fromEntity($tickerData),
			$this->tickerDataProvider->getTickerDatas($ticker, $fromDate, $toDate)
		);

		return new JsonResponse($tickerDatas);
	}
}
