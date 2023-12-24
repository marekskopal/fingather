<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Transaction;
use FinGather\Utils\DateTimeUtils;

final readonly class TransactionDto
{
	public function __construct(
		public int $id,
		public int $assetId,
		public int $brokerId,
		public TransactionActionTypeEnum $actionType,
		public string $created,
		public Decimal $units,
		public Decimal $priceUnit,
		public int $currencyId,
		public Decimal $feeConversion,
		public ?string $notes,
		public ?string $importIdentifier,
	) {
	}

	public static function fromEntity(Transaction $transaction): self
	{
		return new self(
			id: $transaction->getId(),
			assetId: $transaction->getAsset()->getId(),
			brokerId: $transaction->getBroker()->getId(),
			actionType: TransactionActionTypeEnum::from($transaction->getActionType()),
			created: DateTimeUtils::formatZulu($transaction->getCreated()),
			units: new Decimal($transaction->getUnits()),
			priceUnit: new Decimal($transaction->getPriceUnit()),
			currencyId: $transaction->getCurrency()->getId(),
			feeConversion: new Decimal($transaction->getFeeConversion()),
			notes: $transaction->getNotes(),
			importIdentifier: $transaction->getImportIdentifier(),
		);
	}
}
