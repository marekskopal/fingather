<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Currency;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<Currency> */
final class CurrencyRepository extends AbstractRepository
{
	/** @return list<Currency> */
	public function findCurrencies(): array
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
