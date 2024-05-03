<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use FinGather\Model\Entity\Currency;

final class CurrencyFixture
{
	public static function getCurrency(
		?string $code = null,
		?string $name = null,
		?string $symbol = null,
		?Currency $multiplyCurrency = null,
		?int $multiplier = null,
		?bool $isSelectable = null,
	): Currency {
		return new Currency(
			code: $code ?? 'USD',
			name: $name ?? 'US Dollar',
			symbol: $symbol ?? '$',
			multiplyCurrency: $multiplyCurrency,
			multiplier: $multiplier ?? 1,
			isSelectable: $isSelectable ?? true,
		);
	}
}
