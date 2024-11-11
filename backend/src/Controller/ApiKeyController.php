<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\ApiKeyDto;
use FinGather\Model\Entity\ApiKey;
use FinGather\Model\Entity\Enum\ApiKeyTypeEnum;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\ApiKeyProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteDelete;
use MarekSkopal\Router\Attribute\RouteGet;
use MarekSkopal\Router\Attribute\RoutePost;
use MarekSkopal\Router\Attribute\RoutePut;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ApiKeyController
{
	public function __construct(
		private readonly ApiKeyProvider $apiKeyProvider,
		private readonly PortfolioProvider $portfolioProvider,
		private readonly RequestService $requestService,
	) {
	}

	#[RouteGet(Routes::ApiKeys->value)]
	public function actionGetApiKeys(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		$apiKeys = array_map(
			fn (ApiKey $apiKey): ApiKeyDto => ApiKeyDto::fromEntity($apiKey),
			$this->apiKeyProvider->getApiKeys($user, $portfolio),
		);

		return new JsonResponse($apiKeys);
	}

	#[RouteGet(Routes::ApiKey->value)]
	public function actionGetApiKey(ServerRequestInterface $request, int $apiKeyId): ResponseInterface
	{
		if ($apiKeyId < 1) {
			return new NotFoundResponse('API key id is required.');
		}

		$apiKey = $this->apiKeyProvider->getApiKey(
			apiKeyId: $apiKeyId,
			user: $this->requestService->getUser($request),
		);
		if ($apiKey === null) {
			return new NotFoundResponse('API key with id "' . $apiKeyId . '" was not found.');
		}

		return new JsonResponse(ApiKeyDto::fromEntity($apiKey));
	}

	#[RoutePost(Routes::ApiKeys->value)]
	public function actionPostApiKey(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		/** @var array{type: value-of<ApiKeyTypeEnum>, apiKey: string} $requestBody */
		$requestBody = json_decode($request->getBody()->getContents(), associative: true);

		return new JsonResponse(ApiKeyDto::fromEntity($this->apiKeyProvider->createApiKey(
			user: $user,
			portfolio: $portfolio,
			type: ApiKeyTypeEnum::from($requestBody['type']),
			apiKey: $requestBody['apiKey'],
		)));
	}

	#[RoutePut(Routes::ApiKey->value)]
	public function actionPutApiKey(ServerRequestInterface $request, int $apiKeyId): ResponseInterface
	{
		if ($apiKeyId < 1) {
			return new NotFoundResponse('API key id is required.');
		}

		$apiKey = $this->apiKeyProvider->getApiKey(
			apiKeyId: $apiKeyId,
			user: $this->requestService->getUser($request),
		);
		if ($apiKey === null) {
			return new NotFoundResponse('API key with id "' . $apiKeyId . '" was not found.');
		}

		/** @var array{apiKey: string} $requestBody */
		$requestBody = json_decode($request->getBody()->getContents(), associative: true);

		return new JsonResponse(ApiKeyDto::fromEntity($this->apiKeyProvider->updateApiKey(
			apiKeyEntity: $apiKey,
			apiKey: $requestBody['apiKey'],
		)));
	}

	#[RouteDelete(Routes::ApiKey->value)]
	public function actionDeleteApiKey(ServerRequestInterface $request, int $apiKeyId): ResponseInterface
	{
		if ($apiKeyId < 1) {
			return new NotFoundResponse('API key id is required.');
		}

		$apiKey = $this->apiKeyProvider->getApiKey(
			apiKeyId: $apiKeyId,
			user: $this->requestService->getUser($request),
		);
		if ($apiKey === null) {
			return new NotFoundResponse('API key with id "' . $apiKeyId . '" was not found.');
		}

		$this->apiKeyProvider->deleteApiKey($apiKey);

		return new OkResponse();
	}
}
