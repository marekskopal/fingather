<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\ApiKey;

/** @extends ARepository<ApiKey> */
final class ApiKeyRepository extends ARepository
{
	/** @return iterable<ApiKey> */
	public function findApiKeys(?int $userId, ?int $portfolioId): iterable
	{
		$apiKeysSelect = $this->select();

		if ($userId !== null) {
			$apiKeysSelect->where('user_id', $userId);
		}

		if ($portfolioId !== null) {
			$apiKeysSelect->where('portfolio_id', $portfolioId);
		}

		return $apiKeysSelect->fetchAll();
	}

	public function findApiKey(int $apiKeyId): ?ApiKey
	{
		return $this->select()->where('id', $apiKeyId)->fetchOne();
	}
}
