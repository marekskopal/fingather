<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

use FinGather\Dto\AbstractGroupWithGroupDataDto;

final readonly class McpAllocationItemDto
{
	public function __construct(
		public string $name,
		public float $percentage,
		public string $value,
		public string $transactionValue,
		public string $gain,
		public float $gainPercentage,
		public float $gainPercentagePerAnnum,
		public string $dividendYield,
		public float $dividendYieldPercentage,
		public string $fxImpact,
		public float $fxImpactPercentage,
		public string $return,
		public float $returnPercentage,
		public float $returnPercentagePerAnnum,
	) {
	}

	public static function fromGroupWithGroupData(AbstractGroupWithGroupDataDto $dto): self
	{
		return new self(
			name: $dto->name,
			percentage: $dto->percentage,
			value: (string) $dto->groupData->value,
			transactionValue: (string) $dto->groupData->transactionValue,
			gain: (string) $dto->groupData->gain,
			gainPercentage: $dto->groupData->gainPercentage,
			gainPercentagePerAnnum: $dto->groupData->gainPercentagePerAnnum,
			dividendYield: (string) $dto->groupData->dividendYield,
			dividendYieldPercentage: $dto->groupData->dividendYieldPercentage,
			fxImpact: (string) $dto->groupData->fxImpact,
			fxImpactPercentage: $dto->groupData->fxImpactPercentage,
			return: (string) $dto->groupData->return,
			returnPercentage: $dto->groupData->returnPercentage,
			returnPercentagePerAnnum: $dto->groupData->returnPercentagePerAnnum,
		);
	}
}
