<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\McpApiKey;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\McpApiKeyRepository;
use FinGather\Service\Encryption\EncryptionServiceInterface;
use Iterator;

final readonly class McpApiKeyProvider implements McpApiKeyProviderInterface
{
	public function __construct(private McpApiKeyRepository $mcpApiKeyRepository, private EncryptionServiceInterface $encryptionService,)
	{
	}

	/** @return Iterator<McpApiKey> */
	public function getMcpApiKeys(User $user): Iterator
	{
		foreach ($this->mcpApiKeyRepository->findMcpApiKeys($user->id) as $mcpApiKey) {
			$this->decryptApiKey($mcpApiKey);

			yield $mcpApiKey;
		}
	}

	public function getMcpApiKey(int $id, User $user): ?McpApiKey
	{
		$mcpApiKey = $this->mcpApiKeyRepository->findMcpApiKey($id, $user->id);
		if ($mcpApiKey !== null) {
			$this->decryptApiKey($mcpApiKey);
		}

		return $mcpApiKey;
	}

	public function createMcpApiKey(User $user, string $name): McpApiKey
	{
		$rawKey = $this->generateRawKey();

		$entity = new McpApiKey(
			user: $user,
			name: $name,
			apiKey: $this->encryptionService->encrypt($rawKey),
			keyHash: hash('sha256', $rawKey),
			createdAt: new DateTimeImmutable(),
		);

		$this->mcpApiKeyRepository->persist($entity);

		$entity->apiKey = $rawKey;

		return $entity;
	}

	public function findUserByKey(string $rawKey): ?User
	{
		$keyHash = hash('sha256', $rawKey);
		$mcpApiKey = $this->mcpApiKeyRepository->findMcpApiKeyByHash($keyHash);

		return $mcpApiKey?->user;
	}

	public function deleteMcpApiKey(McpApiKey $mcpApiKey): void
	{
		$this->mcpApiKeyRepository->delete($mcpApiKey);
	}

	private function decryptApiKey(McpApiKey $mcpApiKey): void
	{
		$mcpApiKey->apiKey = $this->encryptionService->decrypt($mcpApiKey->apiKey);
	}

	private function generateRawKey(): string
	{
		return bin2hex(random_bytes(32));
	}
}
