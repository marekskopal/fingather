<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use DateTimeImmutable;
use Decimal\Decimal;
use MarekSkopal\Cycle\Decimal\ColumnDecimal;

class ADataEntity extends AEntity
{
	public function __construct(
		#[RefersTo(target: User::class)]
		protected User $user,
		#[RefersTo(target: Portfolio::class)]
		protected Portfolio $portfolio,
		#[Column(type: 'timestamp')]
		protected DateTimeImmutable $date,
		#[ColumnDecimal(precision: 11, scale: 2)]
		protected Decimal $value,
		#[ColumnDecimal(precision: 11, scale: 2)]
		protected Decimal $transactionValue,
		#[ColumnDecimal(precision: 11, scale: 2)]
		protected Decimal $gain,
		#[Column(type: 'float')]
		protected float $gainPercentage,
		#[Column(type: 'float')]
		protected float $gainPercentagePerAnnum,
		#[ColumnDecimal(precision: 11, scale: 2)]
		protected Decimal $realizedGain,
		#[ColumnDecimal(precision: 11, scale: 2)]
		protected Decimal $dividendGain,
		#[Column(type: 'float')]
		protected float $dividendGainPercentage,
		#[Column(type: 'float')]
		protected float $dividendGainPercentagePerAnnum,
		#[ColumnDecimal(precision: 11, scale: 2)]
		protected Decimal $fxImpact,
		#[Column(type: 'float')]
		protected float $fxImpactPercentage,
		#[Column(type: 'float')]
		protected float $fxImpactPercentagePerAnnum,
		#[ColumnDecimal(precision: 11, scale: 2)]
		protected Decimal $return,
		#[Column(type: 'float')]
		protected float $returnPercentage,
		#[Column(type: 'float')]
		protected float $returnPercentagePerAnnum,
		#[ColumnDecimal(precision: 12, scale: 2)]
		public Decimal $tax,
		#[ColumnDecimal(precision: 12, scale: 2)]
		public Decimal $fee,
	) {
	}

	public function getDate(): DateTimeImmutable
	{
		return $this->date;
	}

	public function getValue(): Decimal
	{
		return $this->value;
	}

	public function getTransactionValue(): Decimal
	{
		return $this->transactionValue;
	}

	public function getGain(): Decimal
	{
		return $this->gain;
	}

	public function getGainPercentage(): float
	{
		return $this->gainPercentage;
	}

	public function getGainPercentagePerAnnum(): float
	{
		return $this->gainPercentagePerAnnum;
	}

	public function getRealizedGain(): Decimal
	{
		return $this->realizedGain;
	}

	public function getDividendGain(): Decimal
	{
		return $this->dividendGain;
	}

	public function getDividendGainPercentage(): float
	{
		return $this->dividendGainPercentage;
	}

	public function getDividendGainPercentagePerAnnum(): float
	{
		return $this->dividendGainPercentagePerAnnum;
	}

	public function getFxImpact(): Decimal
	{
		return $this->fxImpact;
	}

	public function getFxImpactPercentage(): float
	{
		return $this->fxImpactPercentage;
	}

	public function getFxImpactPercentagePerAnnum(): float
	{
		return $this->fxImpactPercentagePerAnnum;
	}

	public function getReturn(): Decimal
	{
		return $this->return;
	}

	public function getReturnPercentage(): float
	{
		return $this->returnPercentage;
	}

	public function getReturnPercentagePerAnnum(): float
	{
		return $this->returnPercentagePerAnnum;
	}

	public function getTax(): Decimal
	{
		return $this->tax;
	}

	public function getFee(): Decimal
	{
		return $this->fee;
	}
}
