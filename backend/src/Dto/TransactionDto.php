<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Enum\TransactionCreateTypeEnum;
use FinGather\Model\Entity\Transaction;
use FinGather\Utils\DateTimeUtils;

final readonly class TransactionDto
{
	public function __construct(
		public int $id,
		public int $assetId,
		public ?int $brokerId,
		public TransactionActionTypeEnum $actionType,
		public string $actionCreated,
		public TransactionCreateTypeEnum $createType,
		public string $created,
		public string $modified,
		public Decimal $units,
		public Decimal $price,
		public int $currencyId,
		public Decimal $tax,
		public int $taxCurrencyId,
		public Decimal $fee,
		public int $feeCurrencyId,
		public ?string $notes,
		public ?string $importIdentifier,
		public TickerDto $ticker,
	) {
	}

	public static function fromEntity(Transaction $transaction): self
	{
		return new self(
			id: $transaction->id,
			assetId: $transaction->asset->id,
			brokerId: $transaction->brokerId,
			actionType: $transaction->actionType,
			actionCreated: DateTimeUtils::formatZulu($transaction->actionCreated),
			createType: $transaction->createType,
			created: DateTimeUtils::formatZulu($transaction->created),
			modified: DateTimeUtils::formatZulu($transaction->modified),
			units: $transaction->units,
			price: $transaction->price,
			currencyId: $transaction->currency->id,
			tax:$transaction->tax,
			taxCurrencyId: $transaction->taxCurrency->id,
			fee: $transaction->fee,
			feeCurrencyId: $transaction->feeCurrency->id,
			notes: $transaction->notes,
			importIdentifier: $transaction->importIdentifier,
			ticker: TickerDto::fromEntity($transaction->asset->ticker),
		);
	}
}
