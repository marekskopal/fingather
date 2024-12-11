<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Industry;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<Industry> */
final class IndustryRepository extends AbstractRepository
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
