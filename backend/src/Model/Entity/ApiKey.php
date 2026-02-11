<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use FinGather\Model\Entity\Enum\ApiKeyTypeEnum;
use FinGather\Model\Repository\ApiKeyRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\ColumnEnum;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;
use MarekSkopal\ORM\Enum\Type;

#[Entity(repositoryClass: ApiKeyRepository::class)]
class ApiKey extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: User::class)]
		public readonly User $user,
		#[ManyToOne(entityClass: Portfolio::class)]
		public readonly Portfolio $portfolio,
		#[ColumnEnum(enum: ApiKeyTypeEnum::class)]
		public readonly ApiKeyTypeEnum $type,
		#[Column(type: Type::String)]
		public string $apiKey,
		#[Column(type: Type::Text, nullable: true)]
		public ?string $userKey = null,
	) {
	}
}
