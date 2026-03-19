<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\ApiKey;
use FinGather\Model\Entity\Enum\ApiKeyTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use Iterator;

interface ApiKeyProviderInterface
{
	/** @return Iterator<ApiKey> */
	public function getApiKeys(?User $user = null, ?Portfolio $portfolio = null): Iterator;

	public function getApiKey(int $apiKeyId, ?User $user = null): ?ApiKey;

	public function createApiKey(User $user, Portfolio $portfolio, ApiKeyTypeEnum $type, string $apiKey, ?string $userKey = null): ApiKey;

	public function updateApiKey(ApiKey $apiKeyEntity, string $apiKey, ?string $userKey = null): ApiKey;

	public function deleteApiKey(ApiKey $apiKey): void;
}
