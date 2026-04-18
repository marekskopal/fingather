<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Mcp\McpUserContextInterface;
use FinGather\Mcp\Server\FinGatherServer;
use FinGather\Mcp\Session\RedisSessionStore;
use FinGather\OAuth\AuthorizationServiceInterface;
use FinGather\Response\ErrorResponse;
use FinGather\Route\Routes;
use MarekSkopal\Router\Attribute\RouteDelete;
use MarekSkopal\Router\Attribute\RouteGet;
use MarekSkopal\Router\Attribute\RoutePost;
use Mcp\Server\Transport\StreamableHttpTransport;
use Predis\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

final readonly class McpController
{
	public function __construct(
		private AuthorizationServiceInterface $authorizationService,
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
		$token = $this->extractBearerToken($request);
		if ($token === null) {
			return $this->createUnauthorizedResponse('Missing or invalid Authorization header. Expected: Bearer <access_token>');
		}

		try {
			$user = $this->authorizationService->validateAccessToken($token);
		} catch (RuntimeException) {
			return $this->createUnauthorizedResponse('Invalid or expired access token.');
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

	private function createUnauthorizedResponse(string $message): ErrorResponse
	{
		$host = (string) getenv('PROXY_HOST');
		$port = (int) getenv('PROXY_PORT_SSL');
		$baseUrl = 'https://' . $host . ($port !== 443 ? ':' . $port : '');

		return new ErrorResponse($message, 401, [
			'WWW-Authenticate' => 'Bearer resource_metadata="' . $baseUrl . Routes::OAuthResourceMetadata->value . '"',
		]);
	}
}
