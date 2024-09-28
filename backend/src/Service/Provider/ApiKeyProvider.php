<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\ApiKey;
use FinGather\Model\Entity\Enum\ApiKeyTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\ApiKeyRepository;

class ApiKeyProvider
{
	public function __construct(private readonly ApiKeyRepository $apiKeyRepository)
	{
	}

	/** @return list<ApiKey> */
	public function getApiKeys(?User $user = null, ?Portfolio $portfolio = null): array
	{
		return $this->apiKeyRepository->findApiKeys(
			userId: $user?->getId(),
			portfolioId: $portfolio?->getId(),
		);
	}

	public function getApiKey(int $apiKeyId, ?User $user = null): ?ApiKey
	{
		return $this->apiKeyRepository->findApiKey($apiKeyId, $user?->getId());
	}

	public function createApiKey(User $user, Portfolio $portfolio, ApiKeyTypeEnum $type, string $apiKey): ApiKey
	{
		$apiKey = new ApiKey(user: $user, portfolio: $portfolio, type: $type, apiKey: $apiKey);
		$this->apiKeyRepository->persist($apiKey);

		return $apiKey;
	}

	public function updateApiKey(ApiKey $apiKeyEntity, string $apiKey): ApiKey
	{
		$apiKeyEntity->setApiKey($apiKey);
		$this->apiKeyRepository->persist($apiKeyEntity);

		return $apiKeyEntity;
	}

	public function deleteApiKey(ApiKey $apiKey): void
	{
		$this->apiKeyRepository->delete($apiKey);
	}
}
