<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Service\Provider\BrokerProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function Safe\json_decode;

class BrokerController
{
	public function __construct(private readonly BrokerProvider $brokerProvider, private readonly RequestService $requestService)
	{
	}

	public function actionGetBrokers(ServerRequestInterface $request): ResponseInterface
	{
		return new JsonResponse($this->brokerProvider->getBrokers($this->requestService->getUser($request)));
	}

	/** @param array{brokerId: string} $args */
	public function actionGetBroker(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$brokerId = (int) $args['brokerId'];
		if ($brokerId < 1) {
			return new NotFoundResponse('Broker id is required.');
		}

		$broker = $this->brokerProvider->getBroker(
			user: $this->requestService->getUser($request),
			brokerId: $brokerId,
		);
		if ($broker === null) {
			return new NotFoundResponse('Broker with id "' . $brokerId . '" was not found.');
		}

		return new JsonResponse($broker);
	}

	public function actionCreateBroker(ServerRequestInterface $request): ResponseInterface
	{
		/** @var array{name: string, importType: value-of<BrokerImportTypeEnum>} $requestBody */
		$requestBody = json_decode($request->getBody()->getContents(), assoc: true);

		return new JsonResponse($this->brokerProvider->createBroker(
			user: $this->requestService->getUser($request),
			name: $requestBody['name'],
			importType: BrokerImportTypeEnum::from($requestBody['importType']),
		));
	}

	/** @param array{brokerId: string} $args */
	public function actionUpdateBroker(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$brokerId = (int) $args['brokerId'];
		if ($brokerId < 1) {
			return new NotFoundResponse('Broker id is required.');
		}

		$broker = $this->brokerProvider->getBroker(
			user: $this->requestService->getUser($request),
			brokerId: $brokerId,
		);
		if ($broker === null) {
			return new NotFoundResponse('Broker with id "' . $brokerId . '" was not found.');
		}

		/** @var array{name: string, importType: value-of<BrokerImportTypeEnum>} $requestBody */
		$requestBody = json_decode($request->getBody()->getContents(), assoc: true);

		return new JsonResponse($this->brokerProvider->updateBroker(
			broker: $broker,
			name: $requestBody['name'],
			importType: BrokerImportTypeEnum::from($requestBody['importType']),
		));
	}

	/** @param array{brokerId: string} $args */
	public function actionDeleteBroker(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$brokerId = (int) $args['brokerId'];
		if ($brokerId < 1) {
			return new NotFoundResponse('Broker id is required.');
		}

		$broker = $this->brokerProvider->getBroker(
			user: $this->requestService->getUser($request),
			brokerId: $brokerId,
		);
		if ($broker === null) {
			return new NotFoundResponse('Broker with id "' . $brokerId . '" was not found.');
		}

		$this->brokerProvider->deleteBroker($broker);

		return new OkResponse();
	}
}
