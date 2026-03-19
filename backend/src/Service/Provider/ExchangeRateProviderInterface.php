<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Currency;

interface ExchangeRateProviderInterface
{
	public function getExchangeRate(DateTimeImmutable $date, Currency $currencyFrom, Currency $currencyTo): Decimal;

	public function updateExchangeRates(Currency $currencyTo): ?DateTimeImmutable;
}
