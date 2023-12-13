<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\ExchangeRate;
use Safe\DateTime;

/** @extends ARepository<ExchangeRate> */
class ExchangeRateRepository extends ARepository
{
	public function findExchangeRate(DateTime $date, int $currencyId): ?ExchangeRate
	{
		return $this->findOne([
			'date' => $date,
			'currency_id' => $currencyId,
		]);
	}
}
