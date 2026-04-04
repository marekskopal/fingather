<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Service\DataCalculator\Dcf\Dto\DcfHistoryPointDto;
use FinGather\Service\DataCalculator\Dcf\Dto\DcfInputs;

final readonly class TickerDcfValuationInputsDto
{
	/** @param list<TickerDcfValuationHistoryPointDto> $history */
	public function __construct(
		public int $sharesOutstanding,
		public ?int $latestRevenue,
		public ?int $latestFcfe,
		public ?float $quarterlyRevenueGrowth,
		public ?float $beta,
		public array $history,
	) {
	}

	public static function fromInputs(DcfInputs $inputs): self
	{
		return new self(
			sharesOutstanding: $inputs->sharesOutstanding,
			latestRevenue: $inputs->latestRevenue,
			latestFcfe: $inputs->latestFcfe,
			quarterlyRevenueGrowth: $inputs->quarterlyRevenueGrowth,
			beta: $inputs->beta,
			history: array_map(
				static fn (DcfHistoryPointDto $point): TickerDcfValuationHistoryPointDto => TickerDcfValuationHistoryPointDto::fromDto($point),
				$inputs->history,
			),
		);
	}
}
