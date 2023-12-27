<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\CurrencyDto;
use FinGather\Model\Entity\Currency;
use FinGather\Service\Provider\CurrencyProvider;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CurrencyController
{
	public function __construct(private readonly CurrencyProvider $currencyProvider)
	{
	}

	public function actionGetCurrencies(ServerRequestInterface $request): ResponseInterface
	{
		$brokers = array_map(
			fn (Currency $currency): CurrencyDto => CurrencyDto::fromEntity($currency),
			iterator_to_array($this->currencyProvider->getCurrencies())
		);

		return new JsonResponse($brokers);
	}
}
