<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use DateTimeImmutable;
use FinGather\Service\DataCalculator\Dto\AssetDataDto;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;

interface DataCalculatorInterface
{
	/** @param array<int, AssetDataDto> $assets */
	public function calculate(
		array $assets,
		DateTimeImmutable $dateTime,
		DateTimeImmutable $firstTransactionActionCreated,
	): CalculatedDataDto;
}
