<?php

declare(strict_types=1);

namespace FinGather\Service\Provider\Dto;

class DividendDataIntervalDto
{
	/** @param array<DividendDataAssetDto> $dividendDataAssets */
	public function __construct(public readonly string $interval, public readonly array $dividendDataAssets,)
	{
	}
}
