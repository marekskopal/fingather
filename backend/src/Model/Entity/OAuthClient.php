<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use DateTimeImmutable;
use FinGather\Model\Repository\OAuthClientRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;
use MarekSkopal\ORM\Enum\Type;

#[Entity(repositoryClass: OAuthClientRepository::class, table: 'oauth_clients')]
class OAuthClient extends AEntity
{
	public function __construct(
		#[Column(type: Type::String, size: 128)]
		public readonly string $clientId,
		#[Column(type: Type::String)]
		public string $clientName,
		#[Column(type: Type::String)]
		public string $redirectUris,
		#[ManyToOne(entityClass: User::class, nullable: true)]
		public readonly ?User $user,
		#[Column(type: Type::Timestamp)]
		public readonly DateTimeImmutable $createdAt,
	) {
	}
}
