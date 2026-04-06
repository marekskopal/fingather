<?php

declare(strict_types=1);

namespace FinGather\Tests\Dto;

use FinGather\Dto\McpApiKeyDto;
use FinGather\Model\Entity\McpApiKey;
use FinGather\Model\Entity\User;
use FinGather\Tests\Fixtures\Model\Entity\McpApiKeyFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(McpApiKeyDto::class)]
#[UsesClass(McpApiKey::class)]
#[UsesClass(User::class)]
final class McpApiKeyDtoTest extends TestCase
{
	public function testFromEntityMasksApiKey(): void
	{
		$mcpApiKey = McpApiKeyFixture::getMcpApiKey(apiKey: 'a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2');

		$dto = McpApiKeyDto::fromEntity($mcpApiKey);

		// Last 4 characters should be visible, rest masked
		self::assertStringEndsWith('a1b2', $dto->apiKey);
		self::assertStringStartsWith('****', $dto->apiKey);
		self::assertSame(64, strlen($dto->apiKey));
	}

	public function testFromEntitySetsAllFields(): void
	{
		$mcpApiKey = McpApiKeyFixture::getMcpApiKey(name: 'My Key');

		$dto = McpApiKeyDto::fromEntity($mcpApiKey);

		self::assertSame($mcpApiKey->id, $dto->id);
		self::assertSame('My Key', $dto->name);
		self::assertSame('2026-01-01 00:00:00', $dto->createdAt);
	}
}
