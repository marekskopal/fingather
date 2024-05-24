<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\TickerIndustry;

/** @extends ARepository<TickerIndustry> */
final class TickerIndustryRepository extends ARepository
{
	public function findTickerIndustryByName(string $name): ?TickerIndustry
	{
		return $this->findOne([
			'name' => $name,
		]);
	}

	public function findOthersTickerIndustry(): TickerIndustry
	{
		$othersTickerIndustry = $this->findOne([
			'is_others' => true,
		]);
		assert($othersTickerIndustry instanceof TickerIndustry);
		return $othersTickerIndustry;
	}
}
