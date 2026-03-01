<?php

declare(strict_types=1);

namespace FinGather\Dto;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Enum\DcaPlanTargetTypeEnum;

/**
 * @implements ArrayFactoryInterface<array{
 *     targetType: value-of<DcaPlanTargetTypeEnum>,
 *     assetId: int|null,
 *     groupId: int|null,
 *     strategyId: int|null,
 *     amount: float,
 *     currencyId: int,
 *     intervalMonths: int,
 *     startDate: string,
 *     endDate: string|null,
 * }>
 */
final readonly class DcaPlanUpdateDto implements ArrayFactoryInterface
{
	public function __construct(
		public DcaPlanTargetTypeEnum $targetType,
		public ?int $assetId,
		public ?int $groupId,
		public ?int $strategyId,
		public Decimal $amount,
		public int $currencyId,
		public int $intervalMonths,
		public DateTimeImmutable $startDate,
		public ?DateTimeImmutable $endDate,
	) {
	}

	public static function fromArray(array $data): static
	{
		return new self(
			targetType: DcaPlanTargetTypeEnum::from($data['targetType']),
			assetId: $data['assetId'] ?? null,
			groupId: $data['groupId'] ?? null,
			strategyId: $data['strategyId'] ?? null,
			amount: new Decimal((string) $data['amount']),
			currencyId: $data['currencyId'],
			intervalMonths: $data['intervalMonths'],
			startDate: new DateTimeImmutable($data['startDate']),
			endDate: $data['endDate'] !== null ? new DateTimeImmutable($data['endDate']) : null,
		);
	}
}
