<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Currency;
use FinGather\Model\Repository\CurrencyRepository;
use Iterator;

final readonly class CurrencyProvider
{
	public function __construct(private CurrencyRepository $currencyRepository)
	{
	}

	/** @return Iterator<Currency> */
	public function getCurrencies(): Iterator
	{
		return $this->currencyRepository->findCurrencies();
	}

	public function getCurrency(int $currencyId): ?Currency
	{
		return $this->currencyRepository->findCurrency($currencyId);
	}
}
