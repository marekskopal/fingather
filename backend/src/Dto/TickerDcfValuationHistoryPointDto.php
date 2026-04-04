<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Service\DataCalculator\Dcf\Dto\DcfHistoryPointDto;

final readonly class TickerDcfValuationHistoryPointDto
{
	public function __construct(
		public string $fiscalDate,
		public ?int $freeCashFlow,
		public ?int $revenue,
	) {
	}

	public static function fromDto(DcfHistoryPointDto $dto): self
	{
		return new self(
			fiscalDate: $dto->fiscalDate->format('Y-m-d'),
			freeCashFlow: $dto->freeCashFlow,
			revenue: $dto->revenue,
		);
	}
}
