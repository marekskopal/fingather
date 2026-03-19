<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Currency;
use Iterator;

interface CurrencyProviderInterface
{
	/** @return Iterator<Currency> */
	public function getCurrencies(): Iterator;

	public function getCurrency(int $currencyId): ?Currency;

	public function getCurrencyByCode(string $code): ?Currency;
}
