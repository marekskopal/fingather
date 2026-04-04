<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Service\Provider\Dto\DcfValuationView;

final readonly class TickerDcfValuationDto
{
	public function __construct(
		public int $tickerId,
		public string $intrinsicValue,
		public string $equityValue,
		public ?string $currentPrice,
		public ?float $valuationDiffPercent,
		public ?string $valuationStatus,
		public TickerDcfValuationInputsDto $inputs,
		public TickerDcfValuationAssumptionsDto $assumptions,
		public TickerDcfValuationProjectionDto $projection,
	) {
	}

	public static function fromView(DcfValuationView $view): self
	{
		return new self(
			tickerId: $view->ticker->id,
			intrinsicValue: (string) $view->result->intrinsicValuePerShare,
			equityValue: (string) $view->result->equityValue,
			currentPrice: $view->result->currentPrice !== null ? (string) $view->result->currentPrice : null,
			valuationDiffPercent: $view->result->valuationDiffPercent,
			valuationStatus: $view->result->valuationStatus?->value,
			inputs: TickerDcfValuationInputsDto::fromInputs($view->inputs),
			assumptions: TickerDcfValuationAssumptionsDto::fromResult($view->result),
			projection: TickerDcfValuationProjectionDto::fromResult($view->result),
		);
	}
}
