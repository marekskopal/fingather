<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use Cycle\ORM\Parser\Typecast;
use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Repository\CountryDataRepository;
use MarekSkopal\Cycle\Decimal\DecimalTypecast;

#[Entity(repository: CountryDataRepository::class, typecast: [
	Typecast::class,
	DecimalTypecast::class,
])]
class CountryData extends ADataEntity
{
	public function __construct(
		#[RefersTo(target: Country::class)]
		private Country $country,
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
