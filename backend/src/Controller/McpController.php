<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Mcp\McpUserContextInterface;
use FinGather\Mcp\Server\FinGatherServer;
use FinGather\Mcp\Session\RedisSessionStore;
use FinGather\Response\ErrorResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\McpApiKeyProviderInterface;
use MarekSkopal\Router\Attribute\RouteDelete;
use MarekSkopal\Router\Attribute\RouteGet;
use MarekSkopal\Router\Attribute\RoutePost;
use Mcp\Server\Transport\StreamableHttpTransport;
use Predis\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class McpController
{
	public function __construct(
		private McpApiKeyProviderInterface $mcpApiKeyProvider,
		private McpUserContextInterface $userContext,
		private FinGatherServer $server,
		private ClientInterface $redis,
	) {
	}

	#[RouteGet(Routes::Mcp->value)]
	public function actionGetMcp(ServerRequestInterface $request): ResponseInterface
	{
		return $this->handleMcp($request);
	}

	#[RoutePost(Routes::Mcp->value)]
	public function actionPostMcp(ServerRequestInterface $request): ResponseInterface
	{
		return $this->handleMcp($request);
	}

	#[RouteDelete(Routes::Mcp->value)]
	public function actionDeleteMcp(ServerRequestInterface $request): ResponseInterface
	{
		return $this->handleMcp($request);
	}

	private function handleMcp(ServerRequestInterface $request): ResponseInterface
	{
		$rawKey = $this->extractBearerToken($request);
		if ($rawKey === null) {
			return new ErrorResponse('Missing or invalid Authorization header. Expected: Bearer <mcp-api-key>', 401);
		}

		$user = $this->mcpApiKeyProvider->findUserByKey($rawKey);
		if ($user === null) {
			return new ErrorResponse('Invalid MCP API key.', 401);
		}

		$this->userContext->setUser($user);

		$sessionStore = new RedisSessionStore($this->redis);
		$mcpServer = $this->server->build($sessionStore);
		$transport = new StreamableHttpTransport($request);

		return $mcpServer->run($transport);
	}

	private function extractBearerToken(ServerRequestInterface $request): ?string
	{
		$header = $request->getHeaderLine('Authorization');
		if ($header === '' || !str_starts_with($header, 'Bearer ')) {
			return null;
		}

		$token = substr($header, 7);

		return $token !== '' ? $token : null;
	}
}
