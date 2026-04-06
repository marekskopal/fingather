<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\McpApiKey;
use Iterator;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<McpApiKey> */
final class McpApiKeyRepository extends AbstractRepository
{
	/** @return Iterator<McpApiKey> */
	public function findMcpApiKeys(int $userId): Iterator
	{
		return $this->select()->where(['user_id' => $userId])->fetchAll();
	}

	public function findMcpApiKey(int $id, int $userId): ?McpApiKey
	{
		return $this->select()->where(['id' => $id, 'user_id' => $userId])->fetchOne();
	}

	public function findMcpApiKeyByHash(string $keyHash): ?McpApiKey
	{
		return $this->select()->where(['key_hash' => $keyHash])->fetchOne();
	}
}
