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
			assetId: $transaction->getAsset()->id,
			brokerId: $transaction->getBrokerId(),
			actionType: $transaction->getActionType(),
			actionCreated: DateTimeUtils::formatZulu($transaction->getActionCreated()),
			createType: $transaction->getCreateType(),
			created: DateTimeUtils::formatZulu($transaction->getCreated()),
			modified: DateTimeUtils::formatZulu($transaction->getModified()),
			units: $transaction->getUnits(),
			price: $transaction->getPrice(),
			currencyId: $transaction->getCurrency()->id,
			tax:$transaction->getTax(),
			taxCurrencyId: $transaction->getTaxCurrency()->id,
			fee: $transaction->getFee(),
			feeCurrencyId: $transaction->getFeeCurrency()->id,
			notes: $transaction->getNotes(),
			importIdentifier: $transaction->getImportIdentifier(),
			ticker: TickerDto::fromEntity($transaction->getAsset()->getTicker()),
		);
	}
}
