<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Industry;

/** @extends ARepository<Industry> */
final class IndustryRepository extends ARepository
{
	public function findIndustryByName(string $name): ?Industry
	{
		return $this->findOne([
			'name' => $name,
		]);
	}

	public function findOthersIndustry(): Industry
	{
		$othersTickerIndustry = $this->findOne([
			'is_others' => true,
		]);
		assert($othersTickerIndustry instanceof Industry);
		return $othersTickerIndustry;
	}
}
