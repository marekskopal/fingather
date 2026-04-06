<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

use FinGather\Model\Entity\Transaction;

final readonly class McpTransactionDto
{
	public function __construct(
		public int $transactionId,
		public string $ticker,
		public int $assetId,
		public string $actionType,
		public string $date,
		public string $units,
		public string $price,
		public string $currency,
		public string $fee,
		public string $tax,
		public ?string $notes,
	) {
	}

	public static function fromEntity(Transaction $transaction): self
	{
		return new self(
			transactionId: $transaction->id,
			ticker: $transaction->asset->ticker->ticker,
			assetId: $transaction->asset->id,
			actionType: $transaction->actionType->value,
			date: $transaction->actionCreated->format('Y-m-d'),
			units: (string) $transaction->units,
			price: (string) $transaction->price,
			currency: $transaction->currency->code,
			fee: (string) $transaction->fee,
			tax: (string) $transaction->tax,
			notes: $transaction->notes,
		);
	}
}
