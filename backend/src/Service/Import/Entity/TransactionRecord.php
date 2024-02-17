<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Entity;

use Decimal\Decimal;
use Safe\DateTimeImmutable;

readonly class TransactionRecord
{
	public function __construct(
		public ?string $ticker = null,
		public ?string $marketMic = null,
		public ?string $actionType = null,
		public ?DateTimeImmutable $created = null,
		public ?Decimal $units = null,
		public ?Decimal $price = null,
		public ?string $currency = null,
		public ?Decimal $tax = null,
		public ?Decimal $fee = null,
		public ?string $notes = null,
		public ?string $importIdentifier = null,
	) {
	}
}
