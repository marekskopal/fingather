<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\ApiKey;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\ApiKeyRepository;

class ApiKeyProvider
{
	public function __construct(private readonly ApiKeyRepository $apiKeyRepository)
	{
	}

	/** @return iterable<ApiKey> */
	public function getApiKeys(?User $user = null, ?Portfolio $portfolio = null): iterable
	{
		return $this->apiKeyRepository->findApiKeys(
			userId: $user?->getId(),
			portfolioId: $portfolio?->getId(),
		);
	}

	public function getApiKey(int $apiKeyId): ?ApiKey
	{
		return $this->apiKeyRepository->findApiKey($apiKeyId);
	}
}
