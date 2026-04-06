<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

use FinGather\Model\Entity\Transaction;

final readonly class McpTransactionCreatedDto
{
	public function __construct(
		public int $transactionId,
		public string $ticker,
		public string $actionType,
		public string $date,
		public string $units,
		public string $price,
		public string $currency,
	) {
	}

	public static function fromEntity(Transaction $transaction): self
	{
		return new self(
			transactionId: $transaction->id,
			ticker: $transaction->asset->ticker->ticker,
			actionType: $transaction->actionType->value,
			date: $transaction->actionCreated->format('Y-m-d'),
			units: (string) $transaction->units,
			price: (string) $transaction->price,
			currency: $transaction->currency->code,
		);
	}
}
