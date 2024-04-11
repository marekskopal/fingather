<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use Cycle\ORM\Parser\Typecast;
use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Repository\AssetDataRepository;
use MarekSkopal\Cycle\Decimal\ColumnDecimal;
use MarekSkopal\Cycle\Decimal\DecimalTypecast;

#[Entity(repository: AssetDataRepository::class, typecast: [
	Typecast::class,
	DecimalTypecast::class,
])]
class AssetData extends AEntity
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
		#[ColumnDecimal(precision: 20, scale: 10)]
		public Decimal $price,
		#[ColumnDecimal(precision: 18, scale: 8)]
		public Decimal $units,
		#[ColumnDecimal(precision: 12, scale: 2)]
		public Decimal $value,
		#[ColumnDecimal(precision: 12, scale: 2)]
		public Decimal $transactionValue,
		#[ColumnDecimal(precision: 12, scale: 2)]
		public Decimal $transactionValueDefaultCurrency,
		#[ColumnDecimal(precision: 12, scale: 2)]
		public Decimal $gain,
		#[ColumnDecimal(precision: 12, scale: 2)]
		public Decimal $gainDefaultCurrency,
		#[Column(type: 'float')]
		public float $gainPercentage,
		#[Column(type: 'float')]
		public float $gainPercentagePerAnnum,
		#[ColumnDecimal(precision: 12, scale: 2)]
		public Decimal $realizedGain,
		#[ColumnDecimal(precision: 12, scale: 2)]
		public Decimal $realizedGainDefaultCurrency,
		#[ColumnDecimal(precision: 12, scale: 2)]
		public Decimal $dividendGain,
		#[ColumnDecimal(precision: 12, scale: 2)]
		public Decimal $dividendGainDefaultCurrency,
		#[Column(type: 'float')]
		public float $dividendGainPercentage,
		#[Column(type: 'float')]
		public float $dividendGainPercentagePerAnnum,
		#[ColumnDecimal(precision: 12, scale: 2)]
		public Decimal $fxImpact,
		#[Column(type: 'float')]
		public float $fxImpactPercentage,
		#[Column(type: 'float')]
		public float $fxImpactPercentagePerAnnum,
		#[ColumnDecimal(precision: 12, scale: 2)]
		public Decimal $return,
		#[Column(type: 'float')]
		public float $returnPercentage,
		#[Column(type: 'float')]
		public float $returnPercentagePerAnnum,
		#[ColumnDecimal(precision: 12, scale: 2)]
		public Decimal $tax,
		#[ColumnDecimal(precision: 12, scale: 2)]
		public Decimal $taxDefaultCurrency,
		#[ColumnDecimal(precision: 12, scale: 2)]
		public Decimal $fee,
		#[ColumnDecimal(precision: 12, scale: 2)]
		public Decimal $feeDefaultCurrency,
		#[Column(type: 'timestamp')]
		public DateTimeImmutable $firstTransactionActionCreated,
	) {
	}

	public function isOpen(): bool
	{
		return $this->units->isPositive() && !$this->units->isZero();
	}

	public function isClosed(): bool
	{
		return !$this->isOpen();
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

	public function getAsset(): Asset
	{
		return $this->asset;
	}

	public function setAsset(Asset $asset): void
	{
		$this->asset = $asset;
	}

	public function getDate(): DateTimeImmutable
	{
		return $this->date;
	}

	public function setDate(DateTimeImmutable $date): void
	{
		$this->date = $date;
	}

	public function getPrice(): Decimal
	{
		return $this->price;
	}

	public function setPrice(Decimal $price): void
	{
		$this->price = $price;
	}

	public function getUnits(): Decimal
	{
		return $this->units;
	}

	public function setUnits(Decimal $units): void
	{
		$this->units = $units;
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

	public function getTransactionValueDefaultCurrency(): Decimal
	{
		return $this->transactionValueDefaultCurrency;
	}

	public function setTransactionValueDefaultCurrency(Decimal $transactionValueDefaultCurrency): void
	{
		$this->transactionValueDefaultCurrency = $transactionValueDefaultCurrency;
	}

	public function getGain(): Decimal
	{
		return $this->gain;
	}

	public function setGain(Decimal $gain): void
	{
		$this->gain = $gain;
	}

	public function getGainDefaultCurrency(): Decimal
	{
		return $this->gainDefaultCurrency;
	}

	public function setGainDefaultCurrency(Decimal $gainDefaultCurrency): void
	{
		$this->gainDefaultCurrency = $gainDefaultCurrency;
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

	public function getRealizedGain(): Decimal
	{
		return $this->realizedGain;
	}

	public function setRealizedGain(Decimal $realizedGain): void
	{
		$this->realizedGain = $realizedGain;
	}

	public function getRealizedGainDefaultCurrency(): Decimal
	{
		return $this->realizedGainDefaultCurrency;
	}

	public function setRealizedGainDefaultCurrency(Decimal $realizedGainDefaultCurrency): void
	{
		$this->realizedGainDefaultCurrency = $realizedGainDefaultCurrency;
	}

	public function getDividendGain(): Decimal
	{
		return $this->dividendGain;
	}

	public function setDividendGain(Decimal $dividendGain): void
	{
		$this->dividendGain = $dividendGain;
	}

	public function getDividendGainDefaultCurrency(): Decimal
	{
		return $this->dividendGainDefaultCurrency;
	}

	public function setDividendGainDefaultCurrency(Decimal $dividendGainDefaultCurrency): void
	{
		$this->dividendGainDefaultCurrency = $dividendGainDefaultCurrency;
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

	public function getTax(): Decimal
	{
		return $this->tax;
	}

	public function setTax(Decimal $tax): void
	{
		$this->tax = $tax;
	}

	public function getTaxDefaultCurrency(): Decimal
	{
		return $this->taxDefaultCurrency;
	}

	public function setTaxDefaultCurrency(Decimal $taxDefaultCurrency): void
	{
		$this->taxDefaultCurrency = $taxDefaultCurrency;
	}

	public function getFee(): Decimal
	{
		return $this->fee;
	}

	public function setFee(Decimal $fee): void
	{
		$this->fee = $fee;
	}

	public function getFeeDefaultCurrency(): Decimal
	{
		return $this->feeDefaultCurrency;
	}

	public function setFeeDefaultCurrency(Decimal $feeDefaultCurrency): void
	{
		$this->feeDefaultCurrency = $feeDefaultCurrency;
	}

	public function getFirstTransactionActionCreated(): DateTimeImmutable
	{
		return $this->firstTransactionActionCreated;
	}

	public function setFirstTransactionActionCreated(DateTimeImmutable $firstTransactionActionCreated): void
	{
		$this->firstTransactionActionCreated = $firstTransactionActionCreated;
	}
}
