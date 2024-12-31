<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\ApiKey;
use Iterator;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<ApiKey> */
final class ApiKeyRepository extends AbstractRepository
{
	/** @return Iterator<ApiKey> */
	public function findApiKeys(?int $userId, ?int $portfolioId): Iterator
	{
		$apiKeysSelect = $this->select();

		if ($userId !== null) {
			$apiKeysSelect->where(['user_id' => $userId]);
		}

		if ($portfolioId !== null) {
			$apiKeysSelect->where(['portfolio_id' => $portfolioId]);
		}

		return $apiKeysSelect->fetchAll();
	}

	public function findApiKey(int $apiKeyId, ?int $userId): ?ApiKey
	{
		$apiKeySelect = $this->select()
			->where(['id' => $apiKeyId]);

		if ($userId !== null) {
			$apiKeySelect->where(['user_id' => $userId]);
		}

		return $apiKeySelect->fetchOne();
	}
}
