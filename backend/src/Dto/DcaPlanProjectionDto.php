<?php

declare(strict_types=1);

namespace FinGather\Dto;

final readonly class DcaPlanProjectionDto
{
	/** @param list<DcaPlanProjectionPointDto> $dataPoints */
	public function __construct(public array $dataPoints,)
	{
	}
}
