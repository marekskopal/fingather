<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

use Decimal\Decimal;
use FinGather\Model\Entity\Goal;

final readonly class McpGoalDto
{
	public function __construct(
		public int $goalId,
		public string $type,
		public string $targetValue,
		public string $currentValue,
		public float $progressPercentage,
		public bool $isAchieved,
		public bool $isActive,
		public ?string $achievedAt,
		public ?string $deadline,
	) {
	}

	public static function fromEntity(Goal $goal, Decimal $currentValue): self
	{
		$targetFloat = $goal->targetValue->toFloat();
		$currentFloat = $currentValue->toFloat();

		return new self(
			goalId: $goal->id,
			type: $goal->type->value,
			targetValue: (string) $goal->targetValue,
			currentValue: (string) $currentValue,
			progressPercentage: $targetFloat > 0 ? round($currentFloat / $targetFloat * 100, 2) : 0.0,
			isAchieved: $goal->achievedAt !== null,
			isActive: $goal->isActive,
			achievedAt: $goal->achievedAt?->format('Y-m-d'),
			deadline: $goal->deadline?->format('Y-m-d'),
		);
	}
}
