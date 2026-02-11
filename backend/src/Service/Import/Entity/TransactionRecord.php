<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Entity;

use DateTimeImmutable;
use Decimal\Decimal;

final readonly class TransactionRecord
{
	public function __construct(
		public ?string $ticker = null,
		public ?string $isin = null,
		public ?string $marketMic = null,
		public ?string $actionType = null,
		public ?DateTimeImmutable $created = null,
		public ?Decimal $units = null,
		public ?Decimal $price = null,
		public ?Decimal $total = null,
		public ?string $currency = null,
		public ?Decimal $tax = null,
		public ?string $taxCurrency = null,
		public ?Decimal $fee = null,
		public ?string $feeCurrency = null,
		public ?string $notes = null,
		public ?bool $isAdjusted = null,
		public ?string $importIdentifier = null,
	) {
	}
}
