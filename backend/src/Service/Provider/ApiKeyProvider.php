<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\ApiKey;
use FinGather\Model\Entity\Enum\ApiKeyTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\ApiKeyRepository;
use FinGather\Service\Encryption\EncryptionServiceInterface;
use Iterator;

final readonly class ApiKeyProvider implements ApiKeyProviderInterface
{
	public function __construct(private ApiKeyRepository $apiKeyRepository, private EncryptionServiceInterface $encryptionService)
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
		$createdApiKey = new ApiKey(
			user: $user,
			portfolio: $portfolio,
			type: $type,
			apiKey: $this->encryptionService->encrypt($apiKey),
			userKey: $userKey !== null ? $this->encryptionService->encrypt($userKey) : null,
		);
		$this->apiKeyRepository->persist($createdApiKey);

		return $createdApiKey;
	}

	public function updateApiKey(ApiKey $apiKeyEntity, string $apiKey, ?string $userKey = null): ApiKey
	{
		$updatedApiKey = new ApiKey(
			user: $apiKeyEntity->user,
			portfolio: $apiKeyEntity->portfolio,
			type: $apiKeyEntity->type,
			apiKey: $this->encryptionService->encrypt($apiKey),
			userKey: $userKey !== null ? $this->encryptionService->encrypt($userKey) : null,
		);
		$updatedApiKey->id = $apiKeyEntity->id;
		$this->apiKeyRepository->persist($updatedApiKey);

		return $updatedApiKey;
	}

	public function deleteApiKey(ApiKey $apiKey): void
	{
		$this->apiKeyRepository->delete($apiKey);
	}

	public function decryptApiKeyValue(ApiKey $apiKey): string
	{
		return $this->encryptionService->decrypt($apiKey->apiKey);
	}

	public function decryptUserKeyValue(ApiKey $apiKey): ?string
	{
		if ($apiKey->userKey === null) {
			return null;
		}

		return $this->encryptionService->decrypt($apiKey->userKey);
	}
}
