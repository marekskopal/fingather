<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use Cycle\ORM\Parser\Typecast;
use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Repository\BenchmarkDataRepository;
use MarekSkopal\Cycle\Decimal\ColumnDecimal;
use MarekSkopal\Cycle\Decimal\DecimalTypecast;

#[Entity(repository: BenchmarkDataRepository::class, typecast: [
	Typecast::class,
	DecimalTypecast::class,
])]
class BenchmarkData extends AEntity
{
	public function __construct(
		#[RefersTo(target: User::class)]
		protected User $user,
		#[RefersTo(target: Portfolio::class)]
		private Portfolio $portfolio,
		#[RefersTo(target: Asset::class)]
		protected Asset $asset,
		#[Column(type: 'timestamp')]
		protected DateTimeImmutable $date,
		#[Column(type: 'timestamp')]
		protected DateTimeImmutable $fromDate,
		#[ColumnDecimal(precision: 11, scale: 2)]
		protected Decimal $value,
		#[ColumnDecimal(precision: 18, scale: 8)]
		protected Decimal $units,
	) {
	}

	public function getAsset(): Asset
	{
		return $this->asset;
	}

	public function getDate(): DateTimeImmutable
	{
		return $this->date;
	}

	public function getValue(): Decimal
	{
		return $this->value;
	}

	public function getUnits(): Decimal
	{
		return $this->units;
	}
}
