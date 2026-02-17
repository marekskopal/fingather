<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\ApiKey;
use FinGather\Model\Entity\Enum\ApiKeyTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\ApiKeyRepository;
use Iterator;

final readonly class ApiKeyProvider
{
	public function __construct(private ApiKeyRepository $apiKeyRepository)
	{
	}

	/** @return Iterator<ApiKey> */
	public function getApiKeys(?User $user = null, ?Portfolio $portfolio = null): Iterator
	{
		return $this->apiKeyRepository->findApiKeys(userId: $user?->id, portfolioId: $portfolio?->id);
	}

	public function getApiKey(int $apiKeyId, ?User $user = null): ?ApiKey
	{
		return $this->apiKeyRepository->findApiKey($apiKeyId, $user?->id);
	}

	public function createApiKey(User $user, Portfolio $portfolio, ApiKeyTypeEnum $type, string $apiKey, ?string $userKey = null): ApiKey
	{
		$createdApiKey = new ApiKey(user: $user, portfolio: $portfolio, type: $type, apiKey: $apiKey, userKey: $userKey);
		$this->apiKeyRepository->persist($createdApiKey);

		return $createdApiKey;
	}

	public function updateApiKey(ApiKey $apiKeyEntity, string $apiKey, ?string $userKey = null): ApiKey
	{
		$apiKeyEntity->apiKey = $apiKey;
		$apiKeyEntity->userKey = $userKey;
		$this->apiKeyRepository->persist($apiKeyEntity);

		return $apiKeyEntity;
	}

	public function deleteApiKey(ApiKey $apiKey): void
	{
		$this->apiKeyRepository->delete($apiKey);
	}
}
