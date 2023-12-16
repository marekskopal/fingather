<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\ExchangeRate;
use Safe\DateTimeImmutable;

/** @extends ARepository<ExchangeRate> */
class ExchangeRateRepository extends ARepository
{
	public function findExchangeRate(DateTimeImmutable $date, int $currencyId): ?ExchangeRate
	{
		return $this->findOne([
			'date' => $date,
			'currency_id' => $currencyId,
		]);
	}

	public function findLastExchangeRate(int $currencyId): ?ExchangeRate
	{
		$select = $this->select()
			->where('currency_id', $currencyId);

		$select->orderBy('date', 'DESC');

		return $select->fetchOne();
	}
}
