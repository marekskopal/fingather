<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use DateTimeImmutable;
use FinGather\Model\Repository\GroupDataRepository;

#[Entity(repository: GroupDataRepository::class)]
class GroupData extends ADataEntity
{
	public function __construct(
		#[RefersTo(target: Group::class)]
		private Group $group,
		User $user,
		DateTimeImmutable $date,
		string $value,
		string $transactionValue,
		string $gain,
		float $gainPercentage,
		string $dividendGain,
		float $dividendGainPercentage,
		string $fxImpact,
		float $fxImpactPercentage,
		string $return,
		float $returnPercentage,
		float $performance,
	) {
		parent::__construct(
			$user,
			$date,
			$value,
			$transactionValue,
			$gain,
			$gainPercentage,
			$dividendGain,
			$dividendGainPercentage,
			$fxImpact,
			$fxImpactPercentage,
			$return,
			$returnPercentage,
			$performance
		);
	}

	public function getGroup(): Group
	{
		return $this->group;
	}

	public function setGroup(Group $group): void
	{
		$this->group = $group;
	}
}
