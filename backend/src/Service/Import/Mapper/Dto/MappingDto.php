<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper\Dto;

use Closure;

final readonly class MappingDto
{
	public function __construct(
		public string|Closure|null $ticker = null,
		public string|Closure|null $isin = null,
		public string|Closure|null $marketMic = null,
		public string|Closure|null $actionType = null,
		public string|Closure|null $created = null,
		public string|Closure|null $units = null,
		public string|Closure|null $price = null,
		public string|Closure|null $total = null,
		public string|Closure|null $currency = null,
		public string|Closure|null $tax = null,
		public string|Closure|null $taxCurrency = null,
		public string|Closure|null $fee = null,
		public string|Closure|null $feeCurrency = null,
		public string|Closure|null $notes = null,
		public string|Closure|null $importIdentifier = null,
	) {
	}
}
