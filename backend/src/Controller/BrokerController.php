<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Model\Repository\BrokerRepository;
use FinGather\Response\NotFoundResponse;
use FinGather\Service\Provider\BrokerProvider;
use FinGather\Service\Provider\PortfolioProvider;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class BrokerController
{
	public function __construct(
		private readonly BrokerProvider $brokerProvider,
	) {
	}

	public function actionGetBrokers(ServerRequestInterface $request): ResponseInterface
	{
		return new JsonResponse($this->brokerProvider->getBrokers());
	}

	/**
	 * @param array{brokerId: string} $args
	 */
	public function actionGetBroker(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$brokerId = (int) $args['brokerId'];
		if ($brokerId < 1) {
			return new NotFoundResponse('Broker id is required.');
		}

		$broker = $this->brokerProvider->getBroker($brokerId);
		if ($broker === null) {
			return new NotFoundResponse('Broker with id "' . $brokerId . '" was not found.');
		}

		return new JsonResponse($broker);
	}
}
