<?php

declare(strict_types=1);

namespace FinGather\Service\Provider\Dto;

use FinGather\Service\DataCalculator\Dcf\Dto\DcfValuationStatusEnum;

final readonly class DcfValuationChipDto
{
	public function __construct(
		public ?float $diffPercent,
		public ?DcfValuationStatusEnum $status,
	) {
	}

	public static function empty(): self
	{
		return new self(diffPercent: null, status: null);
	}
}
