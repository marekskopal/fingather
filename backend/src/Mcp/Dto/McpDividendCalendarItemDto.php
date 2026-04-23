<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

use FinGather\Dto\DividendCalendarItemDto;

final readonly class McpDividendCalendarItemDto
{
	public function __construct(
		public int $assetId,
		public string $ticker,
		public string $name,
		public string $exDate,
		public string $amountPerShare,
		public string $units,
		public string $totalAmount,
		public string $totalAmountDefaultCurrency,
	) {
	}

	public static function fromDividendCalendarItem(DividendCalendarItemDto $dto): self
	{
		return new self(
			assetId: $dto->assetId,
			ticker: $dto->ticker->ticker,
			name: $dto->ticker->name,
			exDate: $dto->exDate,
			amountPerShare: (string) $dto->amountPerShare,
			units: (string) $dto->units,
			totalAmount: (string) $dto->totalAmount,
			totalAmountDefaultCurrency: (string) $dto->totalAmountDefaultCurrency,
		);
	}
}
