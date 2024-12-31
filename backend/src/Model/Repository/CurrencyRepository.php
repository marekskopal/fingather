<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Currency;
use Iterator;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<Currency> */
final class CurrencyRepository extends AbstractRepository
{
	/** @return Iterator<Currency> */
	public function findCurrencies(): Iterator
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
