<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\ApiKeyCreateDto;
use FinGather\Dto\ApiKeyDto;
use FinGather\Dto\ApiKeyUpdateDto;
use FinGather\Model\Entity\ApiKey;
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
			iterator_to_array($this->apiKeyProvider->getApiKeys($user, $portfolio), false),
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

		$apiKeyCreateDto = $this->requestService->getRequestBodyDto($request, ApiKeyCreateDto::class);

		return new JsonResponse(ApiKeyDto::fromEntity($this->apiKeyProvider->createApiKey(
			user: $user,
			portfolio: $portfolio,
			type: $apiKeyCreateDto->type,
			apiKey: $apiKeyCreateDto->apiKey,
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

		$apiKeyUpdateDto = $this->requestService->getRequestBodyDto($request, ApiKeyUpdateDto::class);

		return new JsonResponse(ApiKeyDto::fromEntity($this->apiKeyProvider->updateApiKey(
			apiKeyEntity: $apiKey,
			apiKey: $apiKeyUpdateDto->apiKey,
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
