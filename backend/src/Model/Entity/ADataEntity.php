<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use DateTimeImmutable;

class ADataEntity extends AEntity
{
	public function __construct(
		#[RefersTo(target: User::class)]
		protected User $user,
		#[RefersTo(target: Portfolio::class)]
		private Portfolio $portfolio,
		#[Column(type: 'timestamp')]
		protected DateTimeImmutable $date,
		#[Column(type: 'decimal(11,2)')]
		protected string $value,
		#[Column(type: 'decimal(11,2)')]
		protected string $transactionValue,
		#[Column(type: 'decimal(11,2)')]
		protected string $gain,
		#[Column(type: 'float(4,2')]
		protected float $gainPercentage,
		#[Column(type: 'decimal(11,2)')]
		protected string $dividendGain,
		#[Column(type: 'float(4,2')]
		protected float $dividendGainPercentage,
		#[Column(type: 'decimal(11,2)')]
		protected string $fxImpact,
		#[Column(type: 'float(4,2')]
		protected float $fxImpactPercentage,
		#[Column(type: 'decimal(11,2)')]
		protected string $return,
		#[Column(type: 'float(4,2')]
		protected float $returnPercentage,
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

	public function getValue(): string
	{
		return $this->value;
	}

	public function setValue(string $value): void
	{
		$this->value = $value;
	}

	public function getTransactionValue(): string
	{
		return $this->transactionValue;
	}

	public function setTransactionValue(string $transactionValue): void
	{
		$this->transactionValue = $transactionValue;
	}

	public function getGain(): string
	{
		return $this->gain;
	}

	public function setGain(string $gain): void
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

	public function getDividendGain(): string
	{
		return $this->dividendGain;
	}

	public function setDividendGain(string $dividendGain): void
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

	public function getFxImpact(): string
	{
		return $this->fxImpact;
	}

	public function setFxImpact(string $fxImpact): void
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

	public function getReturn(): string
	{
		return $this->return;
	}

	public function setReturn(string $return): void
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
}
