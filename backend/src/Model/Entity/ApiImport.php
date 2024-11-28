<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use DateTimeImmutable;
use FinGather\Model\Entity\Enum\ApiImportStatusEnum;
use FinGather\Model\Repository\ApiImportRepository;
use MarekSkopal\Cycle\Enum\ColumnEnum;

#[Entity(repository: ApiImportRepository::class)]
class ApiImport extends AEntity
{
	public function __construct(
		#[RefersTo(target: User::class)]
		public readonly User $user,
		#[RefersTo(target: Portfolio::class)]
		public readonly Portfolio $portfolio,
		#[RefersTo(target: ApiKey::class, innerKey:'api_key_id')]
		public readonly ApiKey $apiKey,
		#[ColumnEnum(enum: ApiImportStatusEnum::class)]
		public ApiImportStatusEnum $status,
		#[Column(type: 'timestamp')]
		public readonly DateTimeImmutable $dateFrom,
		#[Column(type: 'timestamp')]
		public readonly DateTimeImmutable $dateTo,
		#[Column(type: 'int', nullable: true)]
		public readonly ?int $reportId,
		#[Column(type: 'string', nullable: true)]
		public ?string $error,
	) {
	}
}
