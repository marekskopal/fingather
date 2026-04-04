<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Service\DataCalculator\Dcf\Dto\DcfResult;

final readonly class TickerDcfValuationProjectionDto
{
	/**
	 * @param list<int> $projectedRevenues
	 * @param list<int> $projectedFcfes
	 */
	public function __construct(
		public array $projectedRevenues,
		public array $projectedFcfes,
		public int $terminalFcfe,
		public string $terminalValue,
		public string $discountedTerminalValue,
	) {
	}

	public static function fromResult(DcfResult $result): self
	{
		return new self(
			projectedRevenues: $result->projectedRevenues,
			projectedFcfes: $result->projectedFcfes,
			terminalFcfe: $result->terminalFcfe,
			terminalValue: (string) $result->terminalValue,
			discountedTerminalValue: (string) $result->discountedTerminalValue,
		);
	}
}
