<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use Cycle\ORM\Select\Repository;
use FinGather\Model\Entity\ExchangeRate;
use Safe\DateTime;

/** @extends Repository<ExchangeRate> */
class ExchangeRateRepository extends Repository
{
	public function findExchangeRate(DateTime $date, int $currencyId): ?ExchangeRate
	{
		return $this->findOne([
			'date' => $date,
			'currency_id' => $currencyId,
		]);
	}
}
