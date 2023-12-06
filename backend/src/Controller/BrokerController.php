<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Model\Repository\BrokerRepository;
use FinGather\Service\Provider\PortfolioProvider;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class BrokerController
{
	public function __construct(
		private readonly BrokerRepository $brokerRepository,
	) {
	}

	public function actionGetBrokers(ServerRequestInterface $request): ResponseInterface
	{
		return new JsonResponse(['test' => 'test']);

		//return new AllowedResponse(
		//	$this->aldAclProvider->isAllowedSettings(
		//		$this->requestEntityService->getWorkspaceUserFromRequest($request),
		//	),
		//);
	}

	public function actionGetBroker(ServerRequestInterface $request, array $args): ResponseInterface
	{
		return new JsonResponse($this->brokerRepository->findByPK($args['brokerId']));
	}
}
