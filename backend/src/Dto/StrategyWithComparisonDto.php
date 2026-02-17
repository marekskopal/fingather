<?php

declare(strict_types=1);

namespace FinGather\Dto;

final readonly class StrategyWithComparisonDto
{
	/** @param list<StrategyComparisonItemDto> $comparisonItems */
	public function __construct(public int $id, public string $name, public bool $isDefault, public array $comparisonItems,)
	{
	}
}
