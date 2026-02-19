<?php

declare(strict_types=1);

namespace FinGather\Dto;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Enum\GoalTypeEnum;

/**
 * @implements ArrayFactoryInterface<array{
 *     portfolioId: int,
 *     type: value-of<GoalTypeEnum>,
 *     targetValue: float,
 *     deadline: string|null,
 *     isActive: bool,
 * }>
 */
final readonly class GoalUpdateDto implements ArrayFactoryInterface
{
	public function __construct(
		public int $portfolioId,
		public GoalTypeEnum $type,
		public Decimal $targetValue,
		public ?DateTimeImmutable $deadline,
		public bool $isActive,
	) {
	}

	public static function fromArray(array $data): static
	{
		return new self(
			portfolioId: $data['portfolioId'],
			type: GoalTypeEnum::from($data['type']),
			targetValue: new Decimal((string) $data['targetValue']),
			deadline: $data['deadline'] !== null ? new DateTimeImmutable($data['deadline']) : null,
			isActive: $data['isActive'],
		);
	}
}
