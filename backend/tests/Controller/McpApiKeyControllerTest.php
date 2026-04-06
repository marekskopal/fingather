<?php

declare(strict_types=1);

namespace FinGather\Tests\Controller;

use ArrayIterator;
use FinGather\Controller\McpApiKeyController;
use FinGather\Dto\McpApiKeyDto;
use FinGather\Model\Entity\McpApiKey;
use FinGather\Model\Entity\User;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Service\Provider\McpApiKeyProviderInterface;
use FinGather\Service\Request\RequestService;
use FinGather\Tests\Fixtures\Model\Entity\McpApiKeyFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;

#[CoversClass(McpApiKeyController::class)]
#[UsesClass(McpApiKey::class)]
#[UsesClass(McpApiKeyDto::class)]
#[UsesClass(User::class)]
#[UsesClass(NotFoundResponse::class)]
#[UsesClass(OkResponse::class)]
final class McpApiKeyControllerTest extends TestCase
{
	private McpApiKeyProviderInterface&Stub $mcpApiKeyProvider;

	private McpApiKeyController $controller;

	protected function setUp(): void
	{
		$this->mcpApiKeyProvider = $this::createStub(McpApiKeyProviderInterface::class);

		$requestService = (new ReflectionClass(RequestService::class))->newInstanceWithoutConstructor();

		$user = UserFixture::getUser();
		$request = $this::createStub(ServerRequestInterface::class);
		$request->method('getAttribute')->willReturn($user);

		$this->controller = new McpApiKeyController($this->mcpApiKeyProvider, $requestService);
	}

	public function testGetMcpApiKeysReturnsJsonResponse(): void
	{
		$this->mcpApiKeyProvider->method('getMcpApiKeys')->willReturn(new ArrayIterator([]));

		$request = $this->createAuthenticatedRequest();

		$response = $this->controller->actionGetMcpApiKeys($request);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	public function testGetMcpApiKeysReturnsKeys(): void
	{
		$this->mcpApiKeyProvider->method('getMcpApiKeys')->willReturn(
			new ArrayIterator([McpApiKeyFixture::getMcpApiKey()]),
		);

		$response = $this->controller->actionGetMcpApiKeys($this->createAuthenticatedRequest());

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	public function testGetMcpApiKeyReturnsFullKey(): void
	{
		$this->mcpApiKeyProvider->method('getMcpApiKey')->willReturn(McpApiKeyFixture::getMcpApiKey());

		$response = $this->controller->actionGetMcpApiKey($this->createAuthenticatedRequest(), 1);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	public function testGetMcpApiKeyNotFoundReturnsNotFound(): void
	{
		$this->mcpApiKeyProvider->method('getMcpApiKey')->willReturn(null);

		$response = $this->controller->actionGetMcpApiKey($this->createAuthenticatedRequest(), 1);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	#[TestWith([0])]
	#[TestWith([-1])]
	public function testDeleteMcpApiKeyInvalidIdReturnsNotFound(int $mcpApiKeyId): void
	{
		$response = $this->controller->actionDeleteMcpApiKey(
			$this->createAuthenticatedRequest(),
			$mcpApiKeyId,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testDeleteMcpApiKeyNotFoundReturnsNotFound(): void
	{
		$this->mcpApiKeyProvider->method('getMcpApiKey')->willReturn(null);

		$response = $this->controller->actionDeleteMcpApiKey($this->createAuthenticatedRequest(), 1);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testDeleteMcpApiKeyReturnsOkResponse(): void
	{
		$this->mcpApiKeyProvider->method('getMcpApiKey')->willReturn(McpApiKeyFixture::getMcpApiKey());

		$response = $this->controller->actionDeleteMcpApiKey($this->createAuthenticatedRequest(), 1);

		self::assertInstanceOf(OkResponse::class, $response);
	}

	private function createAuthenticatedRequest(): ServerRequestInterface&Stub
	{
		$request = $this::createStub(ServerRequestInterface::class);
		$request->method('getAttribute')->willReturn(UserFixture::getUser());

		return $request;
	}
}
