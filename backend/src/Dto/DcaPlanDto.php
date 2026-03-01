<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;
use FinGather\Model\Entity\DcaPlan;
use FinGather\Model\Entity\Enum\DcaPlanTargetTypeEnum;
use FinGather\Service\DataCalculator\Dto\ReturnRateDto;
use FinGather\Utils\DateTimeUtils;

final readonly class DcaPlanDto
{
	public function __construct(
		public int $id,
		public DcaPlanTargetTypeEnum $targetType,
		public int $portfolioId,
		public ?int $assetId,
		public ?int $groupId,
		public ?int $strategyId,
		public string $targetName,
		public Decimal $amount,
		public int $currencyId,
		public int $intervalMonths,
		public string $startDate,
		public ?string $endDate,
		public float $annualReturnRate,
		public float $monthlyReturnRate,
		public string $createdAt,
	) {
	}

	public static function fromEntity(DcaPlan $entity, ReturnRateDto $returnRate): self
	{
		$targetName = match ($entity->targetType) {
			DcaPlanTargetTypeEnum::Portfolio => $entity->portfolio->name,
			DcaPlanTargetTypeEnum::Asset => $entity->asset?->ticker->name ?? '',
			DcaPlanTargetTypeEnum::Group => $entity->group->name ?? '',
			DcaPlanTargetTypeEnum::Strategy => $entity->strategy->name ?? '',
		};

		return new self(
			id: $entity->id,
			targetType: $entity->targetType,
			portfolioId: $entity->portfolio->id,
			assetId: $entity->asset?->id,
			groupId: $entity->group?->id,
			strategyId: $entity->strategy?->id,
			targetName: $targetName,
			amount: $entity->amount,
			currencyId: $entity->currency->id,
			intervalMonths: $entity->intervalMonths,
			startDate: DateTimeUtils::formatZulu($entity->startDate),
			endDate: $entity->endDate !== null ? DateTimeUtils::formatZulu($entity->endDate) : null,
			annualReturnRate: $returnRate->annual,
			monthlyReturnRate: $returnRate->monthly,
			createdAt: DateTimeUtils::formatZulu($entity->createdAt),
		);
	}
}
