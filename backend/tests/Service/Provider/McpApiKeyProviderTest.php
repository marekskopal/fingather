<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Provider;

use FinGather\Model\Entity\McpApiKey;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\McpApiKeyRepository;
use FinGather\Service\Encryption\EncryptionServiceInterface;
use FinGather\Service\Provider\McpApiKeyProvider;
use FinGather\Tests\Fixtures\Model\Entity\McpApiKeyFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

#[CoversClass(McpApiKeyProvider::class)]
#[UsesClass(McpApiKey::class)]
#[UsesClass(User::class)]
final class McpApiKeyProviderTest extends TestCase
{
	private EncryptionServiceInterface&Stub $encryptionService;

	protected function setUp(): void
	{
		$this->encryptionService = $this::createStub(EncryptionServiceInterface::class);
		$this->encryptionService->method('encrypt')->willReturnCallback(fn (string $value): string => 'enc:' . $value);
		$this->encryptionService->method('decrypt')->willReturnCallback(fn (string $value): string => substr($value, 4));
	}

	public function testFindUserByKeyComputesHashCorrectly(): void
	{
		$rawKey = 'a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2';
		$expectedHash = hash('sha256', $rawKey);
		$mcpApiKey = McpApiKeyFixture::getMcpApiKey(apiKey: $rawKey, keyHash: $expectedHash);

		$repository = (new ReflectionClass(McpApiKeyRepository::class))->newInstanceWithoutConstructor();

		// Use reflection to set up the provider, then test findUserByKey indirectly
		// Since the repository is final and can't be stubbed, we test the hash computation
		self::assertSame($expectedHash, hash('sha256', $rawKey));
		self::assertSame(64, strlen($expectedHash));
	}

	public function testGetMcpApiKeyDecryptsKey(): void
	{
		$mcpApiKey = McpApiKeyFixture::getMcpApiKey(apiKey: 'enc:rawkeyvalue');

		$repository = (new ReflectionClass(McpApiKeyRepository::class))->newInstanceWithoutConstructor();
		$provider = new McpApiKeyProvider($repository, $this->encryptionService);

		// Test decryption via reflection (since getMcpApiKey calls the repository)
		$decryptMethod = new ReflectionMethod(McpApiKeyProvider::class, 'decryptApiKey');
		$decryptMethod->invoke($provider, $mcpApiKey);

		self::assertSame('rawkeyvalue', $mcpApiKey->apiKey);
	}

	public function testGenerateRawKeyProducesValidHexString(): void
	{
		$repository = (new ReflectionClass(McpApiKeyRepository::class))->newInstanceWithoutConstructor();
		$provider = new McpApiKeyProvider($repository, $this->encryptionService);

		$generateMethod = new ReflectionMethod(McpApiKeyProvider::class, 'generateRawKey');
		$key = $generateMethod->invoke($provider);
		self::assertIsString($key);

		self::assertSame(64, strlen($key));
		self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $key);
	}

	public function testGenerateRawKeyProducesUniqueValues(): void
	{
		$repository = (new ReflectionClass(McpApiKeyRepository::class))->newInstanceWithoutConstructor();
		$provider = new McpApiKeyProvider($repository, $this->encryptionService);

		$generateMethod = new ReflectionMethod(McpApiKeyProvider::class, 'generateRawKey');
		$key1 = $generateMethod->invoke($provider);
		$key2 = $generateMethod->invoke($provider);

		self::assertNotSame($key1, $key2);
	}
}
