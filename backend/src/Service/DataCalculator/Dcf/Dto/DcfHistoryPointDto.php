<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dcf\Dto;

use DateTimeImmutable;

final readonly class DcfHistoryPointDto
{
	public function __construct(
		public DateTimeImmutable $fiscalDate,
		public ?int $freeCashFlow,
		public ?int $revenue,
	) {
	}
}
