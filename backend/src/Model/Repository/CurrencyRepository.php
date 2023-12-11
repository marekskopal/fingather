<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use Cycle\ORM\Select\Repository;
use FinGather\Model\Entity\Currency;

/** @extends Repository<Currency> */
class CurrencyRepository extends Repository
{
	public function findCurrencyByCode(string $code): ?Currency
	{
		return $this->findOne([
			'code' => $code,
		]);
	}
}
