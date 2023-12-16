<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Entity;

use Brick\Math\BigDecimal;
use Safe\DateTimeImmutable;

readonly class TransactionRecord
{
	public function __construct(
		public ?string $ticker = null,
		public ?string $actionType = null,
		public ?DateTimeImmutable $created = null,
		public ?BigDecimal $units = null,
		public ?BigDecimal $priceUnit = null,
		public ?string $currency = null,
		public ?BigDecimal $exchangeRate = null,
		public ?BigDecimal $feeConversion = null,
		public ?string $notes = null,
		public ?string $importIdentifier = null,
		public ?BigDecimal $total = null,
	) {
	}
}
