<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use DateTimeImmutable;
use FinGather\Model\Entity\McpApiKey;
use FinGather\Model\Entity\User;

final class McpApiKeyFixture
{
	/** @api */
	public static function getMcpApiKey(
		?int $id = null,
		?User $user = null,
		?string $name = null,
		?string $apiKey = null,
		?string $keyHash = null,
		?DateTimeImmutable $createdAt = null,
	): McpApiKey {
		$rawKey = $apiKey ?? 'a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2';

		$mcpApiKey = new McpApiKey(
			user: $user ?? UserFixture::getUser(),
			name: $name ?? 'Test MCP Key',
			apiKey: $rawKey,
			keyHash: $keyHash ?? hash('sha256', $rawKey),
			createdAt: $createdAt ?? new DateTimeImmutable('2026-01-01 00:00:00'),
		);

		$mcpApiKey->id = $id ?? 1;

		return $mcpApiKey;
	}
}
