<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Currency;
use FinGather\Model\Repository\CurrencyRepository;

final readonly class CurrencyProvider implements CurrencyProviderInterface
{
	public function __construct(private CurrencyRepository $currencyRepository)
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

	public function getCurrencyByCode(string $code): ?Currency
	{
		return $this->currencyRepository->findCurrencyByCode($code);
	}
}
