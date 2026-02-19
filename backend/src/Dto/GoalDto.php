<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;
use FinGather\Model\Entity\Enum\GoalTypeEnum;
use FinGather\Model\Entity\Goal;
use FinGather\Utils\DateTimeUtils;

final readonly class GoalDto
{
	public function __construct(
		public int $id,
		public int $portfolioId,
		public string $portfolioName,
		public GoalTypeEnum $type,
		public Decimal $targetValue,
		public ?string $deadline,
		public bool $isActive,
		public ?string $achievedAt,
		public Decimal $currentValue,
		public float $progressPercentage,
		public string $createdAt,
	) {
	}

	public static function fromEntity(Goal $entity, Decimal $currentValue, float $progressPercentage): self
	{
		return new self(
			id: $entity->id,
			portfolioId: $entity->portfolio->id,
			portfolioName: $entity->portfolio->name,
			type: $entity->type,
			targetValue: $entity->targetValue,
			deadline: $entity->deadline !== null ? DateTimeUtils::formatZulu($entity->deadline) : null,
			isActive: $entity->isActive,
			achievedAt: $entity->achievedAt !== null ? DateTimeUtils::formatZulu($entity->achievedAt) : null,
			currentValue: $currentValue,
			progressPercentage: $progressPercentage,
			createdAt: DateTimeUtils::formatZulu($entity->createdAt),
		);
	}
}
