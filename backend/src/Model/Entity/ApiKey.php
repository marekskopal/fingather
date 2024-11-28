<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use FinGather\Model\Entity\Enum\ApiKeyTypeEnum;
use FinGather\Model\Repository\ApiKeyRepository;
use MarekSkopal\Cycle\Enum\ColumnEnum;

#[Entity(repository: ApiKeyRepository::class)]
class ApiKey extends AEntity
{
	public function __construct(
		#[RefersTo(target: User::class)]
		public readonly User $user,
		#[RefersTo(target: Portfolio::class)]
		public readonly Portfolio $portfolio,
		#[ColumnEnum(enum: ApiKeyTypeEnum::class)]
		public readonly ApiKeyTypeEnum $type,
		#[Column(type: 'string')]
		public string $apiKey,
	) {
	}
}
