<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use FinGather\Model\Entity\Currency;

final class CurrencyFixture
{
	public static function getCurrency(
		?int $id = null,
		?string $code = null,
		?string $name = null,
		?string $symbol = null,
		?Currency $multiplyCurrency = null,
		?int $multiplier = null,
		?bool $isSelectable = null,
	): Currency {
		$currency = new Currency(
			code: $code ?? 'USD',
			name: $name ?? 'US Dollar',
			symbol: $symbol ?? '$',
			multiplyCurrency: $multiplyCurrency,
			multiplier: $multiplier ?? 1,
			isSelectable: $isSelectable ?? true,
		);

		$currency->id = $id ?? 1;

		return $currency;
	}
}
