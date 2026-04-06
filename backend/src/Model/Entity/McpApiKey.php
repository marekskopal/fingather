<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use DateTimeImmutable;
use FinGather\Model\Repository\McpApiKeyRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;
use MarekSkopal\ORM\Enum\Type;

#[Entity(repositoryClass: McpApiKeyRepository::class)]
class McpApiKey extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: User::class)]
		public readonly User $user,
		#[Column(type: Type::String)]
		public string $name,
		#[Column(type: Type::String)]
		public string $apiKey,
		#[Column(type: Type::String, size: 64)]
		public readonly string $keyHash,
		#[Column(type: Type::Timestamp)]
		public readonly DateTimeImmutable $createdAt,
	) {
	}
}
