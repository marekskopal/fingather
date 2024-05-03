<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use Cycle\ORM\Select\Repository;
use FinGather\Model\Entity\Currency;

/** @extends Repository<Currency> */
final class CurrencyRepository extends Repository
{
	/** @return iterable<Currency> */
	public function findCurrencies(): iterable
	{
		return $this->findAll();
	}

	public function findCurrency(int $currencyId): ?Currency
	{
		return $this->findOne([
			'id' => $currencyId,
		]);
	}

	public function findCurrencyByCode(string $code): ?Currency
	{
		return $this->findOne([
			'code' => $code,
		]);
	}
}
