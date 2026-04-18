<?php

declare(strict_types=1);

namespace FinGather\Tests\Controller;

use FinGather\Controller\OAuthController;
use FinGather\Model\Entity\OAuthClient;
use FinGather\Model\Entity\User;
use FinGather\OAuth\AuthorizationServiceInterface;
use FinGather\OAuth\ClientServiceInterface;
use FinGather\OAuth\OAuthTokenPair;
use FinGather\Response\ErrorResponse;
use FinGather\Service\Request\RequestServiceInterface;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

#[CoversClass(OAuthController::class)]
#[UsesClass(User::class)]
#[UsesClass(OAuthClient::class)]
#[UsesClass(OAuthTokenPair::class)]
#[UsesClass(ErrorResponse::class)]
final class OAuthControllerTest extends TestCase
{
	private AuthorizationServiceInterface&Stub $authorizationService;
	private ClientServiceInterface&Stub $clientService;
	private RequestServiceInterface&Stub $requestService;
	private OAuthController $controller;

	protected function setUp(): void
	{
		$this->authorizationService = $this->createStub(AuthorizationServiceInterface::class);
		$this->clientService = $this->createStub(ClientServiceInterface::class);
		$this->requestService = $this->createStub(RequestServiceInterface::class);

		$this->controller = new OAuthController(
			$this->authorizationService,
			$this->clientService,
			$this->requestService,
		);
	}

	public function testGetMetadataReturnsCorrectStructure(): void
	{
		$request = (new ServerRequest())->withUri(new Uri('https://example.com/.well-known/oauth-authorization-server/api/mcp'));

		$response = $this->controller->actionGetMetadata($request);

		self::assertInstanceOf(JsonResponse::class, $response);
		self::assertSame(200, $response->getStatusCode());

		/** @var array<string, mixed> $body */
		$body = json_decode((string) $response->getBody(), true);
		self::assertSame('https://example.com/api/mcp', $body['issuer']);
		self::assertSame('https://example.com/oauth/authorize', $body['authorization_endpoint']);
		self::assertArrayHasKey('token_endpoint', $body);
		self::assertArrayHasKey('registration_endpoint', $body);
		self::assertSame(['code'], $body['response_types_supported']);
		self::assertSame(['S256'], $body['code_challenge_methods_supported']);
	}

	public function testGetResourceMetadataReturnsCorrectStructure(): void
	{
		$request = (new ServerRequest())->withUri(new Uri('https://example.com/.well-known/oauth-protected-resource/api/mcp'));

		$response = $this->controller->actionGetResourceMetadata($request);

		self::assertInstanceOf(JsonResponse::class, $response);

		/** @var array<string, mixed> $body */
		$body = json_decode((string) $response->getBody(), true);
		self::assertSame('https://example.com/api/mcp', $body['resource']);
		self::assertSame(['https://example.com/api/mcp'], $body['authorization_servers']);
	}

	public function testPostRegisterReturns201(): void
	{
		$client = new OAuthClient(
			clientId: 'test-client-id',
			clientName: 'Test Client',
			redirectUris: '["http://localhost:3000/callback"]',
			user: null,
			createdAt: new \DateTimeImmutable(),
		);

		$this->clientService->method('registerClient')->willReturn($client);

		$request = (new ServerRequest())
			->withUri(new Uri('https://example.com/api/mcp/oauth/register'))
			->withHeader('Content-Type', 'application/json')
			->withBody(new \Laminas\Diactoros\Stream('php://temp', 'r+'));

		$request->getBody()->write(json_encode([
			'client_name' => 'Test Client',
			'redirect_uris' => ['http://localhost:3000/callback'],
		]));
		$request->getBody()->rewind();

		$response = $this->controller->actionPostRegister($request);

		self::assertInstanceOf(JsonResponse::class, $response);
		self::assertSame(201, $response->getStatusCode());

		/** @var array<string, mixed> $body */
		$body = json_decode((string) $response->getBody(), true);
		self::assertSame('test-client-id', $body['client_id']);
		self::assertSame('Test Client', $body['client_name']);
		self::assertSame('none', $body['token_endpoint_auth_method']);
	}

	public function testPostRegisterRejectsNonJsonContentType(): void
	{
		$request = (new ServerRequest())
			->withUri(new Uri('https://example.com/api/mcp/oauth/register'))
			->withHeader('Content-Type', 'text/plain');

		$response = $this->controller->actionPostRegister($request);

		self::assertInstanceOf(ErrorResponse::class, $response);
		self::assertSame(400, $response->getStatusCode());
	}

	public function testPostTokenReturnsTokenPair(): void
	{
		$tokenPair = new OAuthTokenPair(
			accessToken: 'access-token-123',
			refreshToken: 'refresh-token-456',
			expiresIn: 3600,
		);

		$this->authorizationService->method('exchangeCode')->willReturn($tokenPair);
		$this->requestService->method('getRequestBody')->willReturn([
			'grant_type' => 'authorization_code',
			'code' => 'auth-code',
			'code_verifier' => 'verifier',
			'client_id' => 'client-123',
			'redirect_uri' => 'http://localhost/callback',
		]);

		$request = new ServerRequest();

		$response = $this->controller->actionPostToken($request);

		self::assertInstanceOf(JsonResponse::class, $response);
		self::assertSame(200, $response->getStatusCode());

		/** @var array<string, mixed> $body */
		$body = json_decode((string) $response->getBody(), true);
		self::assertSame('access-token-123', $body['access_token']);
		self::assertSame('refresh-token-456', $body['refresh_token']);
		self::assertSame(3600, $body['expires_in']);
		self::assertSame('Bearer', $body['token_type']);
	}

	public function testPostTokenReturnsErrorForUnsupportedGrantType(): void
	{
		$this->requestService->method('getRequestBody')->willReturn([
			'grant_type' => 'unsupported',
		]);

		$request = new ServerRequest();

		$response = $this->controller->actionPostToken($request);

		self::assertInstanceOf(JsonResponse::class, $response);
		self::assertSame(400, $response->getStatusCode());

		/** @var array<string, mixed> $body */
		$body = json_decode((string) $response->getBody(), true);
		self::assertSame('invalid_grant', $body['error']);
	}

	public function testPostAuthorizeCreatesAuthCode(): void
	{
		$user = UserFixture::getUser();

		$this->requestService->method('getUser')->willReturn($user);
		$this->requestService->method('getRequestBody')->willReturn([
			'clientId' => 'client-123',
			'redirectUri' => 'http://localhost/callback',
			'codeChallenge' => 'challenge-abc',
			'codeChallengeMethod' => 'S256',
			'state' => 'state-xyz',
		]);

		$client = new OAuthClient(
			clientId: 'client-123',
			clientName: 'Test',
			redirectUris: '["http://localhost/callback"]',
			user: null,
			createdAt: new \DateTimeImmutable(),
		);

		$this->clientService->method('findByClientId')->willReturn($client);
		$this->clientService->method('validateRedirectUri')->willReturn(true);
		$this->authorizationService->method('createAuthorizationCode')->willReturn('auth-code-123');

		$request = new ServerRequest();

		$response = $this->controller->actionPostAuthorize($request);

		self::assertInstanceOf(JsonResponse::class, $response);
		self::assertSame(200, $response->getStatusCode());

		/** @var array<string, mixed> $body */
		$body = json_decode((string) $response->getBody(), true);
		self::assertSame('auth-code-123', $body['code']);
		self::assertSame('http://localhost/callback', $body['redirectUri']);
		self::assertSame('state-xyz', $body['state']);
	}

	public function testPostAuthorizeRejectsInvalidCodeChallengeMethod(): void
	{
		$user = UserFixture::getUser();

		$this->requestService->method('getUser')->willReturn($user);
		$this->requestService->method('getRequestBody')->willReturn([
			'clientId' => 'client-123',
			'redirectUri' => 'http://localhost/callback',
			'codeChallenge' => 'challenge-abc',
			'codeChallengeMethod' => 'plain',
			'state' => '',
		]);

		$request = new ServerRequest();

		$response = $this->controller->actionPostAuthorize($request);

		self::assertInstanceOf(ErrorResponse::class, $response);
		self::assertSame(400, $response->getStatusCode());
	}

	public function testGetClientInfoReturnsClientName(): void
	{
		$client = new OAuthClient(
			clientId: 'client-123',
			clientName: 'My App',
			redirectUris: '["http://localhost/callback"]',
			user: null,
			createdAt: new \DateTimeImmutable(),
		);

		$this->clientService->method('findByClientId')->willReturn($client);

		$request = (new ServerRequest())
			->withUri(new Uri('https://example.com/api/mcp/oauth/client-info?client_id=client-123'))
			->withQueryParams(['client_id' => 'client-123']);

		$response = $this->controller->actionGetClientInfo($request);

		self::assertInstanceOf(JsonResponse::class, $response);
		self::assertSame(200, $response->getStatusCode());

		/** @var array<string, mixed> $body */
		$body = json_decode((string) $response->getBody(), true);
		self::assertSame('My App', $body['clientName']);
	}

	public function testGetClientInfoReturns404ForUnknownClient(): void
	{
		$this->clientService->method('findByClientId')->willReturn(null);

		$request = (new ServerRequest())
			->withUri(new Uri('https://example.com/api/mcp/oauth/client-info?client_id=unknown'))
			->withQueryParams(['client_id' => 'unknown']);

		$response = $this->controller->actionGetClientInfo($request);

		self::assertInstanceOf(ErrorResponse::class, $response);
		self::assertSame(404, $response->getStatusCode());
	}
}
