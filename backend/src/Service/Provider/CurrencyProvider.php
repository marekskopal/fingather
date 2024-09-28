<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Currency;
use FinGather\Model\Repository\CurrencyRepository;

class CurrencyProvider
{
	public function __construct(private readonly CurrencyRepository $currencyRepository)
	{
	}

	/** @return list<Currency> */
	public function getCurrencies(): array
	{
		return $this->currencyRepository->findCurrencies();
	}

	public function getCurrency(int $currencyId): ?Currency
	{
		return $this->currencyRepository->findCurrency($currencyId);
	}
}
