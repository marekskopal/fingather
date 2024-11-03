<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper\Dto;

use Closure;

final readonly class MappingDto
{
	public function __construct(
		/** @var string|Closure(array<string> $record): (string|null)|null */
		public string|Closure|null $ticker = null,
		/** @var string|Closure(array<string> $record): (string|null)|null */
		public string|Closure|null $isin = null,
		/** @var string|Closure(array<string> $record): (string|null)|null */
		public string|Closure|null $marketMic = null,
		/** @var string|Closure(array<string> $record): (string|null)|null */
		public string|Closure|null $actionType = null,
		/** @var string|Closure(array<string> $record): (string|null)|null */
		public string|Closure|null $created = null,
		/** @var string|Closure(array<string> $record): (string|null)|null */
		public string|Closure|null $units = null,
		/** @var string|Closure(array<string> $record): (string|null)|null */
		public string|Closure|null $price = null,
		/** @var string|Closure(array<string> $record): (string|null)|null */
		public string|Closure|null $total = null,
		/** @var string|Closure(array<string> $record): (string|null)|null */
		public string|Closure|null $currency = null,
		/** @var string|Closure(array<string> $record): (string|null)|null */
		public string|Closure|null $tax = null,
		/** @var string|Closure(array<string> $record): (string|null)|null */
		public string|Closure|null $taxCurrency = null,
		/** @var string|Closure(array<string> $record): (string|null)|null */
		public string|Closure|null $fee = null,
		/** @var string|Closure(array<string> $record): (string|null)|null */
		public string|Closure|null $feeCurrency = null,
		/** @var string|Closure(array<string> $record): (string|null)|null */
		public string|Closure|null $notes = null,
		/** @var string|Closure(array<string> $record): (string|null)|null */
		public string|Closure|null $importIdentifier = null,
	) {
	}
}
