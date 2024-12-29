<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use DateTimeImmutable;
use FinGather\Model\Entity\Enum\ApiImportStatusEnum;
use FinGather\Model\Repository\ApiImportRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\ColumnEnum;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;
use MarekSkopal\ORM\Enum\Type;

#[Entity(repositoryClass: ApiImportRepository::class)]
class ApiImport extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: User::class)]
		public readonly User $user,
		#[ManyToOne(entityClass: Portfolio::class)]
		public readonly Portfolio $portfolio,
		#[ManyToOne(entityClass: ApiKey::class)]
		public readonly ApiKey $apiKey,
		#[ColumnEnum(enum: ApiImportStatusEnum::class)]
		public ApiImportStatusEnum $status,
		#[Column(type: Type::Timestamp)]
		public readonly DateTimeImmutable $dateFrom,
		#[Column(type: Type::Timestamp)]
		public readonly DateTimeImmutable $dateTo,
		#[Column(type: Type::Int, nullable: true)]
		public readonly ?int $reportId,
		#[Column(type: Type::String, nullable: true)]
		public ?string $error,
	) {
	}
}
