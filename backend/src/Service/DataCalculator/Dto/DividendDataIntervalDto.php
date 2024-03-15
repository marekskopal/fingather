<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

final readonly class DividendDataIntervalDto
{
	/** @param array<DividendDataAssetDto> $dividendDataAssets */
	public function __construct(public string $interval, public array $dividendDataAssets)
	{
	}
}
