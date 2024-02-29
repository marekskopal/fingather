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
		Portfolio $portfolio,
		DateTimeImmutable $date,
		string $value,
		string $transactionValue,
		string $gain,
		float $gainPercentage,
		float $gainPercentagePerAnnum,
		string $dividendGain,
		float $dividendGainPercentage,
		float $dividendGainPercentagePerAnnum,
		string $fxImpact,
		float $fxImpactPercentage,
		float $fxImpactPercentagePerAnnum,
		string $return,
		float $returnPercentage,
		float $returnPercentagePerAnnum,
	) {
		parent::__construct(
			$user,
			$portfolio,
			$date,
			$value,
			$transactionValue,
			$gain,
			$gainPercentage,
			$gainPercentagePerAnnum,
			$dividendGain,
			$dividendGainPercentage,
			$dividendGainPercentagePerAnnum,
			$fxImpact,
			$fxImpactPercentage,
			$fxImpactPercentagePerAnnum,
			$return,
			$returnPercentage,
			$returnPercentagePerAnnum,
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
