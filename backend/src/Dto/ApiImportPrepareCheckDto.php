<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\ApiKey;

/**
 * @implements ArrayFactoryInterface<array{
 *     userId: int,
 *     portfolioId: int,
 *     apiKeyId: int,
 * }>
 */
final readonly class ApiImportPrepareCheckDto implements ArrayFactoryInterface
{
	public function __construct(public int $userId, public int $portfolioId, public int $apiKeyId,)
	{
	}

	public static function fromArray(array $data): static
	{
		return new self(userId: $data['userId'], portfolioId: $data['portfolioId'], apiKeyId: $data['apiKeyId']);
	}

	public static function fromApiKeyEntity(ApiKey $apiKey): self
	{
		return new self(
			userId: $apiKey->getUser()->getId(),
			portfolioId: $apiKey->getPortfolio()->getId(),
			apiKeyId: $apiKey->getId(),
		);
	}
}
