<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Enum\AlertConditionEnum;
use FinGather\Model\Entity\Enum\AlertRecurrenceEnum;
use FinGather\Model\Entity\Enum\PriceAlertTypeEnum;

/**
 * @implements ArrayFactoryInterface<array{
 *     type: value-of<PriceAlertTypeEnum>,
 *     condition: value-of<AlertConditionEnum>,
 *     targetValue: string,
 *     recurrence: value-of<AlertRecurrenceEnum>,
 *     cooldownHours: int,
 *     portfolioId: int|null,
 *     tickerId: int|null,
 * }>
 */
final readonly class PriceAlertCreateDto implements ArrayFactoryInterface
{
	public function __construct(
		public PriceAlertTypeEnum $type,
		public AlertConditionEnum $condition,
		public string $targetValue,
		public AlertRecurrenceEnum $recurrence,
		public int $cooldownHours,
		public ?int $portfolioId,
		public ?int $tickerId,
	) {
	}

	public static function fromArray(array $data): static
	{
		return new self(
			type: PriceAlertTypeEnum::from($data['type']),
			condition: AlertConditionEnum::from($data['condition']),
			targetValue: $data['targetValue'],
			recurrence: AlertRecurrenceEnum::from($data['recurrence']),
			cooldownHours: $data['cooldownHours'],
			portfolioId: $data['portfolioId'] ?? null,
			tickerId: $data['tickerId'] ?? null,
		);
	}
}
