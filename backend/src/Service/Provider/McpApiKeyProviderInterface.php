<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\McpApiKey;
use FinGather\Model\Entity\User;
use Iterator;

interface McpApiKeyProviderInterface
{
	/** @return Iterator<McpApiKey> */
	public function getMcpApiKeys(User $user): Iterator;

	public function getMcpApiKey(int $id, User $user): ?McpApiKey;

	public function createMcpApiKey(User $user, string $name): McpApiKey;

	public function findUserByKey(string $rawKey): ?User;

	public function deleteMcpApiKey(McpApiKey $mcpApiKey): void;
}
