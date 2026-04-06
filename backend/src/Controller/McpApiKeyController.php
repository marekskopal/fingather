<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\McpApiKeyCreateDto;
use FinGather\Dto\McpApiKeyDto;
use FinGather\Model\Entity\McpApiKey;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\McpApiKeyProviderInterface;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteDelete;
use MarekSkopal\Router\Attribute\RouteGet;
use MarekSkopal\Router\Attribute\RoutePost;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class McpApiKeyController
{
	public function __construct(private McpApiKeyProviderInterface $mcpApiKeyProvider, private RequestService $requestService)
	{
	}

	#[RouteGet(Routes::McpApiKeys->value)]
	public function actionGetMcpApiKeys(ServerRequestInterface $request): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		$keys = array_map(
			fn (McpApiKey $key): McpApiKeyDto => McpApiKeyDto::fromEntity($key),
			iterator_to_array($this->mcpApiKeyProvider->getMcpApiKeys($user), false),
		);

		return new JsonResponse($keys);
	}

	#[RoutePost(Routes::McpApiKeys->value)]
	public function actionPostMcpApiKey(ServerRequestInterface $request): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		$createDto = $this->requestService->getRequestBodyDto($request, McpApiKeyCreateDto::class);

		$entity = $this->mcpApiKeyProvider->createMcpApiKey($user, $createDto->name);

		return new JsonResponse(McpApiKeyDto::fromEntity($entity), 201);
	}

	#[RouteGet(Routes::McpApiKey->value)]
	public function actionGetMcpApiKey(ServerRequestInterface $request, int $mcpApiKeyId): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		$key = $this->mcpApiKeyProvider->getMcpApiKey($mcpApiKeyId, $user);
		if ($key === null) {
			return new NotFoundResponse('MCP API key with id "' . $mcpApiKeyId . '" was not found.');
		}

		return new JsonResponse(['apiKey' => $key->apiKey]);
	}

	#[RouteDelete(Routes::McpApiKey->value)]
	public function actionDeleteMcpApiKey(ServerRequestInterface $request, int $mcpApiKeyId): ResponseInterface
	{
		if ($mcpApiKeyId < 1) {
			return new NotFoundResponse('MCP API key id is required.');
		}

		$user = $this->requestService->getUser($request);

		$key = $this->mcpApiKeyProvider->getMcpApiKey($mcpApiKeyId, $user);
		if ($key === null) {
			return new NotFoundResponse('MCP API key with id "' . $mcpApiKeyId . '" was not found.');
		}

		$this->mcpApiKeyProvider->deleteMcpApiKey($key);

		return new OkResponse();
	}
}
