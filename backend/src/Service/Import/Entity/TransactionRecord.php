<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Entity;

use Safe\DateTimeImmutable;

readonly class TransactionRecord
{
	public function __construct(
		public ?string $ticker = null,
		public ?string $actionType = null,
		public ?DateTimeImmutable $created = null,
		public ?float $units = null,
		public ?float $priceUnit = null,
		public ?string $currency = null,
		public ?float $exchangeRate = null,
		public ?float $feeConversion = null,
		public ?string $notes = null,
		public ?string $importIdentifier = null,
	) {
	}
}
