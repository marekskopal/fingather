<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\OAuth\AuthorizationServiceInterface;
use FinGather\OAuth\ClientServiceInterface;
use FinGather\Response\ErrorResponse;
use FinGather\Route\Routes;
use FinGather\Service\Request\RequestServiceInterface;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteGet;
use MarekSkopal\Router\Attribute\RoutePost;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

use const JSON_THROW_ON_ERROR;

final readonly class OAuthController
{
	public function __construct(
		private AuthorizationServiceInterface $authorizationService,
		private ClientServiceInterface $clientService,
		private RequestServiceInterface $requestService,
	) {
	}

	#[RouteGet(Routes::OAuthMetadata->value)]
	public function actionGetMetadata(ServerRequestInterface $request): ResponseInterface
	{
		$baseUrl = self::getBaseUrl();

		$issuer = $baseUrl . Routes::Mcp->value;

		return new JsonResponse([
			'issuer' => $issuer,
			'authorization_endpoint' => $baseUrl . '/oauth/authorize',
			'token_endpoint' => $baseUrl . Routes::OAuthToken->value,
			'registration_endpoint' => $baseUrl . Routes::OAuthRegister->value,
			'response_types_supported' => ['code'],
			'grant_types_supported' => ['authorization_code', 'refresh_token'],
			'code_challenge_methods_supported' => ['S256'],
			'token_endpoint_auth_methods_supported' => ['none'],
		]);
	}

	#[RouteGet(Routes::OAuthResourceMetadata->value)]
	public function actionGetResourceMetadata(ServerRequestInterface $request): ResponseInterface
	{
		$baseUrl = self::getBaseUrl();

		return new JsonResponse([
			'resource' => $baseUrl . Routes::Mcp->value,
			'authorization_servers' => [$baseUrl . Routes::Mcp->value],
		]);
	}

	#[RouteGet(Routes::OAuthClientInfo->value)]
	public function actionGetClientInfo(ServerRequestInterface $request): ResponseInterface
	{
		/** @var array<string, mixed> $params */
		$params = $request->getQueryParams();
		$clientId = is_string($params['client_id'] ?? null) ? $params['client_id'] : '';

		if ($clientId === '') {
			return new ErrorResponse('client_id is required', 400);
		}

		$client = $this->clientService->findByClientId($clientId);
		if ($client === null) {
			return new ErrorResponse('Unknown client_id', 404);
		}

		return new JsonResponse([
			'clientName' => $client->clientName,
		]);
	}

	#[RoutePost(Routes::OAuthAuthorize->value)]
	public function actionPostAuthorize(ServerRequestInterface $request): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		$body = $this->requestService->getRequestBody($request);

		$clientId = is_string($body['clientId'] ?? null) ? $body['clientId'] : '';
		$redirectUri = is_string($body['redirectUri'] ?? null) ? $body['redirectUri'] : '';
		$codeChallenge = is_string($body['codeChallenge'] ?? null) ? $body['codeChallenge'] : '';
		$codeChallengeMethod = is_string($body['codeChallengeMethod'] ?? null) ? $body['codeChallengeMethod'] : '';
		$state = is_string($body['state'] ?? null) ? $body['state'] : '';

		if ($clientId === '' || $redirectUri === '' || $codeChallenge === '') {
			return new ErrorResponse('client_id, redirect_uri, and code_challenge are required', 400);
		}

		if ($codeChallengeMethod !== 'S256') {
			return new ErrorResponse('code_challenge_method must be "S256"', 400);
		}

		$client = $this->clientService->findByClientId($clientId);
		if ($client === null) {
			return new ErrorResponse('Unknown client_id', 400);
		}

		if (!$this->clientService->validateRedirectUri($clientId, $redirectUri)) {
			return new ErrorResponse('Invalid redirect_uri', 400);
		}

		try {
			$code = $this->authorizationService->createAuthorizationCode(
				$clientId,
				$user->id,
				$codeChallenge,
				$codeChallengeMethod,
				$redirectUri,
			);
		} catch (RuntimeException $e) {
			return new ErrorResponse($e->getMessage(), 400);
		}

		return new JsonResponse([
			'code' => $code,
			'redirectUri' => $redirectUri,
			'state' => $state,
		]);
	}

	#[RoutePost(Routes::OAuthToken->value)]
	public function actionPostToken(ServerRequestInterface $request): ResponseInterface
	{
		/** @var array<string, mixed> $body */
		$body = $request->getParsedBody() ?? [];

		$grantType = is_string($body['grant_type'] ?? null) ? $body['grant_type'] : '';

		try {
			$tokenPair = match ($grantType) {
				'authorization_code' => $this->authorizationService->exchangeCode(
					code: is_string($body['code'] ?? null) ? $body['code'] : '',
					codeVerifier: is_string($body['code_verifier'] ?? null) ? $body['code_verifier'] : '',
					clientId: is_string($body['client_id'] ?? null) ? $body['client_id'] : '',
					redirectUri: is_string($body['redirect_uri'] ?? null) ? $body['redirect_uri'] : '',
				),
				'refresh_token' => $this->authorizationService->refreshToken(
					refreshToken: is_string($body['refresh_token'] ?? null) ? $body['refresh_token'] : '',
					clientId: is_string($body['client_id'] ?? null) ? $body['client_id'] : '',
				),
				default => throw new RuntimeException('Unsupported grant type'),
			};
		} catch (RuntimeException $e) {
			return new JsonResponse([
				'error' => 'invalid_grant',
				'error_description' => $e->getMessage(),
			], 400);
		}

		return new JsonResponse([
			'access_token' => $tokenPair->accessToken,
			'token_type' => $tokenPair->tokenType,
			'expires_in' => $tokenPair->expiresIn,
			'refresh_token' => $tokenPair->refreshToken,
		]);
	}

	#[RoutePost(Routes::OAuthRegister->value)]
	public function actionPostRegister(ServerRequestInterface $request): ResponseInterface
	{
		$contentType = $request->getHeaderLine('Content-Type');
		if (!str_contains($contentType, 'application/json')) {
			return new ErrorResponse('Content-Type must be application/json', 400);
		}

		/** @var array<string, mixed> $body */
		$body = json_decode((string) $request->getBody(), true, 16, JSON_THROW_ON_ERROR);

		$clientName = is_string($body['client_name'] ?? null) ? $body['client_name'] : 'MCP Client';

		$redirectUris = [];
		if (is_array($body['redirect_uris'] ?? null)) {
			foreach ($body['redirect_uris'] as $uri) {
				if (is_string($uri) && $uri !== '') {
					$redirectUris[] = $uri;
				}
			}
		}

		if ($redirectUris === []) {
			return new ErrorResponse('At least one redirect_uri is required', 400);
		}

		$client = $this->clientService->registerClient($clientName, $redirectUris);

		return new JsonResponse([
			'client_id' => $client->clientId,
			'client_name' => $client->clientName,
			'redirect_uris' => json_decode($client->redirectUris, true, 2, JSON_THROW_ON_ERROR),
			'token_endpoint_auth_method' => 'none',
		], 201);
	}

	private static function getBaseUrl(): string
	{
		$host = (string) getenv('PROXY_HOST');
		$port = (int) getenv('PROXY_PORT_SSL');

		return 'https://' . $host . ($port !== 443 ? ':' . $port : '');
	}
}
