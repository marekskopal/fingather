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
		$apiKeys = $this->apiKeyRepository->findApiKeys(userId: $user?->id, portfolioId: $portfolio?->id);
		foreach ($apiKeys as $apiKey) {
			$this->decryptApiKey($apiKey);

			yield $apiKey;
		}
	}

	public function getApiKey(int $apiKeyId, ?User $user = null): ?ApiKey
	{
		$apiKey = $this->apiKeyRepository->findApiKey($apiKeyId, $user?->id);
		if ($apiKey !== null) {
			$this->decryptApiKey($apiKey);
		}

		return $apiKey;
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

		$createdApiKey->apiKey = $apiKey;
		$createdApiKey->userKey = $userKey;

		return $createdApiKey;
	}

	public function updateApiKey(ApiKey $apiKeyEntity, string $apiKey, ?string $userKey = null): ApiKey
	{
		$apiKeyEntity->apiKey = $this->encryptionService->encrypt($apiKey);
		$apiKeyEntity->userKey = $userKey !== null ? $this->encryptionService->encrypt($userKey) : null;
		$this->apiKeyRepository->persist($apiKeyEntity);

		$apiKeyEntity->apiKey = $apiKey;
		$apiKeyEntity->userKey = $userKey;

		return $apiKeyEntity;
	}

	public function deleteApiKey(ApiKey $apiKey): void
	{
		$this->apiKeyRepository->delete($apiKey);
	}

	private function decryptApiKey(ApiKey $apiKey): void
	{
		$apiKey->apiKey = $this->encryptionService->decrypt($apiKey->apiKey);
		if ($apiKey->userKey !== null) {
			$apiKey->userKey = $this->encryptionService->decrypt($apiKey->userKey);
		}
	}
}
