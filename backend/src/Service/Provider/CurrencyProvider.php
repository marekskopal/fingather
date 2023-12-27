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

	/** @return iterable<Currency> */
	public function getCurrencies(): iterable
	{
		return $this->currencyRepository->findCurrencies();
	}

	public function getCurrency(int $currencyId): ?Currency
	{
		return $this->currencyRepository->findCurrency($currencyId);
	}
}
