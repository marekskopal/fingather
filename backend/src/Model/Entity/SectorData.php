<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use Cycle\ORM\Parser\Typecast;
use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Repository\SectorDataRepository;
use MarekSkopal\Cycle\Decimal\DecimalTypecast;

#[Entity(repository: SectorDataRepository::class, typecast: [
	Typecast::class,
	DecimalTypecast::class,
])]
class SectorData extends ADataEntity
{
	public function __construct(
		#[RefersTo(target: Sector::class)]
		private Sector $sector,
		User $user,
		Portfolio $portfolio,
		DateTimeImmutable $date,
		Decimal $value,
		Decimal $transactionValue,
		Decimal $gain,
		float $gainPercentage,
		float $gainPercentagePerAnnum,
		Decimal $realizedGain,
		Decimal $dividendGain,
		float $dividendGainPercentage,
		float $dividendGainPercentagePerAnnum,
		Decimal $fxImpact,
		float $fxImpactPercentage,
		float $fxImpactPercentagePerAnnum,
		Decimal $return,
		float $returnPercentage,
		float $returnPercentagePerAnnum,
		Decimal $tax,
		Decimal $fee,
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
			$realizedGain,
			$dividendGain,
			$dividendGainPercentage,
			$dividendGainPercentagePerAnnum,
			$fxImpact,
			$fxImpactPercentage,
			$fxImpactPercentagePerAnnum,
			$return,
			$returnPercentage,
			$returnPercentagePerAnnum,
			$tax,
			$fee,
		);
	}
}
