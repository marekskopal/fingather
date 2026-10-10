<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Currency;

interface CurrencyProviderInterface
{
	/** @return list<Currency> */
	public function getCurrencies(): array;

	public function getCurrency(int $currencyId): ?Currency;

	public function getCurrencyByCode(string $code): ?Currency;
}
