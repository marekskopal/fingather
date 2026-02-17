<?php

declare(strict_types=1);

namespace FinGather\Dto;

use DateTimeImmutable;
use FinGather\Model\Entity\Enum\AlertConditionEnum;
use FinGather\Model\Entity\Enum\AlertRecurrenceEnum;
use FinGather\Model\Entity\Enum\PriceAlertTypeEnum;
use FinGather\Model\Entity\PriceAlert;

final readonly class PriceAlertDto
{
	public function __construct(
		public int $id,
		public PriceAlertTypeEnum $type,
		public AlertConditionEnum $condition,
		public string $targetValue,
		public AlertRecurrenceEnum $recurrence,
		public int $cooldownHours,
		public bool $isActive,
		public ?DateTimeImmutable $lastTriggeredAt,
		public ?int $portfolioId,
		public ?int $tickerId,
		public ?string $tickerTicker,
		public ?string $tickerName,
	) {
	}

	public static function fromEntity(PriceAlert $entity): self
	{
		return new self(
			id: $entity->id,
			type: $entity->type,
			condition: $entity->condition,
			targetValue: $entity->targetValue->toFixed(8),
			recurrence: $entity->recurrence,
			cooldownHours: $entity->cooldownHours,
			isActive: $entity->isActive,
			lastTriggeredAt: $entity->lastTriggeredAt,
			portfolioId: $entity->portfolio?->id,
			tickerId: $entity->ticker?->id,
			tickerTicker: $entity->ticker?->ticker,
			tickerName: $entity->ticker?->name,
		);
	}
}
