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
	) {
	}

	public function getUser(): User
	{
		return $this->user;
	}

	public function setUser(User $user): void
	{
		$this->user = $user;
	}

	public function getPortfolio(): Portfolio
	{
		return $this->portfolio;
	}

	public function setPortfolio(Portfolio $portfolio): void
	{
		$this->portfolio = $portfolio;
	}

	public function getDate(): DateTimeImmutable
	{
		return $this->date;
	}

	public function setDate(DateTimeImmutable $date): void
	{
		$this->date = $date;
	}

	public function getValue(): Decimal
	{
		return $this->value;
	}

	public function setValue(Decimal $value): void
	{
		$this->value = $value;
	}

	public function getTransactionValue(): Decimal
	{
		return $this->transactionValue;
	}

	public function setTransactionValue(Decimal $transactionValue): void
	{
		$this->transactionValue = $transactionValue;
	}

	public function getGain(): Decimal
	{
		return $this->gain;
	}

	public function setGain(Decimal $gain): void
	{
		$this->gain = $gain;
	}

	public function getGainPercentage(): float
	{
		return $this->gainPercentage;
	}

	public function setGainPercentage(float $gainPercentage): void
	{
		$this->gainPercentage = $gainPercentage;
	}

	public function getGainPercentagePerAnnum(): float
	{
		return $this->gainPercentagePerAnnum;
	}

	public function setGainPercentagePerAnnum(float $gainPercentagePerAnnum): void
	{
		$this->gainPercentagePerAnnum = $gainPercentagePerAnnum;
	}

	public function getDividendGain(): Decimal
	{
		return $this->dividendGain;
	}

	public function setDividendGain(Decimal $dividendGain): void
	{
		$this->dividendGain = $dividendGain;
	}

	public function getDividendGainPercentage(): float
	{
		return $this->dividendGainPercentage;
	}

	public function setDividendGainPercentage(float $dividendGainPercentage): void
	{
		$this->dividendGainPercentage = $dividendGainPercentage;
	}

	public function getDividendGainPercentagePerAnnum(): float
	{
		return $this->dividendGainPercentagePerAnnum;
	}

	public function setDividendGainPercentagePerAnnum(float $dividendGainPercentagePerAnnum): void
	{
		$this->dividendGainPercentagePerAnnum = $dividendGainPercentagePerAnnum;
	}

	public function getFxImpact(): Decimal
	{
		return $this->fxImpact;
	}

	public function setFxImpact(Decimal $fxImpact): void
	{
		$this->fxImpact = $fxImpact;
	}

	public function getFxImpactPercentage(): float
	{
		return $this->fxImpactPercentage;
	}

	public function setFxImpactPercentage(float $fxImpactPercentage): void
	{
		$this->fxImpactPercentage = $fxImpactPercentage;
	}

	public function getFxImpactPercentagePerAnnum(): float
	{
		return $this->fxImpactPercentagePerAnnum;
	}

	public function setFxImpactPercentagePerAnnum(float $fxImpactPercentagePerAnnum): void
	{
		$this->fxImpactPercentagePerAnnum = $fxImpactPercentagePerAnnum;
	}

	public function getReturn(): Decimal
	{
		return $this->return;
	}

	public function setReturn(Decimal $return): void
	{
		$this->return = $return;
	}

	public function getReturnPercentage(): float
	{
		return $this->returnPercentage;
	}

	public function setReturnPercentage(float $returnPercentage): void
	{
		$this->returnPercentage = $returnPercentage;
	}

	public function getReturnPercentagePerAnnum(): float
	{
		return $this->returnPercentagePerAnnum;
	}

	public function setReturnPercentagePerAnnum(float $returnPercentagePerAnnum): void
	{
		$this->returnPercentagePerAnnum = $returnPercentagePerAnnum;
	}
}
