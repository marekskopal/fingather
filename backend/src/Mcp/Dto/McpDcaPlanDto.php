<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

use FinGather\Model\Entity\DcaPlan;
use FinGather\Model\Entity\Enum\DcaPlanTargetTypeEnum;
use FinGather\Service\DataCalculator\Dto\ReturnRateDto;

final readonly class McpDcaPlanDto
{
	/** @param list<McpDcaProjectionPointDto> $projection */
	public function __construct(
		public int $planId,
		public string $targetType,
		public ?string $targetName,
		public string $amount,
		public string $currency,
		public int $intervalMonths,
		public string $startDate,
		public ?string $endDate,
		public float $annualReturnRate,
		public float $monthlyReturnRate,
		public array $projection,
	) {
	}

	/** @param list<McpDcaProjectionPointDto> $projection */
	public static function fromDcaPlan(DcaPlan $plan, ReturnRateDto $returnRate, array $projection): self
	{
		$targetName = match ($plan->targetType) {
			DcaPlanTargetTypeEnum::Portfolio => $plan->portfolio->name,
			DcaPlanTargetTypeEnum::Asset => $plan->asset?->ticker->name,
			DcaPlanTargetTypeEnum::Group => $plan->group?->name,
			DcaPlanTargetTypeEnum::Strategy => $plan->strategy?->name,
		};

		return new self(
			planId: $plan->id,
			targetType: $plan->targetType->value,
			targetName: $targetName,
			amount: (string) $plan->amount,
			currency: $plan->currency->code,
			intervalMonths: $plan->intervalMonths,
			startDate: $plan->startDate->format('Y-m-d'),
			endDate: $plan->endDate?->format('Y-m-d'),
			annualReturnRate: $returnRate->annual,
			monthlyReturnRate: $returnRate->monthly,
			projection: $projection,
		);
	}
}
