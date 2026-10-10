<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\ApiKey;
use FinGather\Model\Entity\Enum\ApiKeyTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;

interface ApiKeyProviderInterface
{
	/** @return list<ApiKey> */
	public function getApiKeys(?User $user = null, ?Portfolio $portfolio = null): array;

	public function getApiKey(int $apiKeyId, ?User $user = null): ?ApiKey;

	public function createApiKey(User $user, Portfolio $portfolio, ApiKeyTypeEnum $type, string $apiKey, ?string $userKey = null): ApiKey;

	public function updateApiKey(ApiKey $apiKeyEntity, string $apiKey, ?string $userKey = null): ApiKey;

	public function setApiKeyError(ApiKey $apiKey, ?string $error): void;

	public function deleteApiKey(ApiKey $apiKey): void;

	public function decryptApiKeyValue(ApiKey $apiKey): string;

	public function decryptUserKeyValue(ApiKey $apiKey): ?string;
}
