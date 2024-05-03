<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\BrokerDto;
use FinGather\Model\Entity\Broker;
use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\BrokerProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteDelete;
use MarekSkopal\Router\Attribute\RouteGet;
use MarekSkopal\Router\Attribute\RoutePost;
use MarekSkopal\Router\Attribute\RoutePut;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function Safe\json_decode;

final class BrokerController
{
	public function __construct(
		private readonly BrokerProvider $brokerProvider,
		private readonly PortfolioProvider $portfolioProvider,
		private readonly RequestService $requestService,
	) {
	}

	#[RouteGet(Routes::Brokers->value)]
	public function actionGetBrokers(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		$brokers = array_map(
			fn (Broker $broker): BrokerDto => BrokerDto::fromEntity($broker),
			iterator_to_array($this->brokerProvider->getBrokers($user, $portfolio)),
		);

		return new JsonResponse($brokers);
	}

	#[RouteGet(Routes::Broker->value)]
	public function actionGetBroker(ServerRequestInterface $request, int $brokerId): ResponseInterface
	{
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

		return new JsonResponse(BrokerDto::fromEntity($broker));
	}

	#[RoutePost(Routes::Brokers->value)]
	public function actionCreateBroker(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		/** @var array{name: string, importType: value-of<BrokerImportTypeEnum>} $requestBody */
		$requestBody = json_decode($request->getBody()->getContents(), assoc: true);

		return new JsonResponse(BrokerDto::fromEntity($this->brokerProvider->createBroker(
			user: $user,
			portfolio: $portfolio,
			name: $requestBody['name'],
			importType: BrokerImportTypeEnum::from($requestBody['importType']),
		)));
	}

	#[RoutePut(Routes::Broker->value)]
	public function actionUpdateBroker(ServerRequestInterface $request, int $brokerId): ResponseInterface
	{
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

		return new JsonResponse(BrokerDto::fromEntity($this->brokerProvider->updateBroker(
			broker: $broker,
			name: $requestBody['name'],
			importType: BrokerImportTypeEnum::from($requestBody['importType']),
		)));
	}

	#[RouteDelete(Routes::Broker->value)]
	public function actionDeleteBroker(ServerRequestInterface $request, int $brokerId): ResponseInterface
	{
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
