<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use Cycle\ORM\Parser\Typecast;
use DateTimeImmutable;
use DateTimeInterface;
use Decimal\Decimal;
use FinGather\Model\Repository\AssetDataRepository;
use MarekSkopal\Cycle\Decimal\ColumnDecimal;
use MarekSkopal\Cycle\Decimal\DecimalTypecast;

#[Entity(repository: AssetDataRepository::class, typecast: [
	Typecast::class,
	DecimalTypecast::class,
])]
class AssetData extends AEntity implements BulkInsertEntityInterface
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
		public Decimal $averagePrice,
		#[ColumnDecimal(precision: 12, scale: 2)]
		public Decimal $averagePriceDefaultCurrency,
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
		public Decimal $dividendYield,
		#[ColumnDecimal(precision: 12, scale: 2)]
		public Decimal $dividendYieldDefaultCurrency,
		#[Column(type: 'float')]
		public float $dividendYieldPercentage,
		#[Column(type: 'float')]
		public float $dividendYieldPercentagePerAnnum,
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

	public function getDate(): DateTimeImmutable
	{
		return $this->date;
	}

	public function getPrice(): Decimal
	{
		return $this->price;
	}

	public function getUnits(): Decimal
	{
		return $this->units;
	}

	public function getValue(): Decimal
	{
		return $this->value;
	}

	public function getTransactionValue(): Decimal
	{
		return $this->transactionValue;
	}

	public function getTransactionValueDefaultCurrency(): Decimal
	{
		return $this->transactionValueDefaultCurrency;
	}

	public function getAveragePrice(): Decimal
	{
		return $this->averagePrice;
	}

	public function getAveragePriceDefaultCurrency(): Decimal
	{
		return $this->averagePriceDefaultCurrency;
	}

	public function getGain(): Decimal
	{
		return $this->gain;
	}

	public function getGainDefaultCurrency(): Decimal
	{
		return $this->gainDefaultCurrency;
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

	public function getRealizedGainDefaultCurrency(): Decimal
	{
		return $this->realizedGainDefaultCurrency;
	}

	public function getdividendYield(): Decimal
	{
		return $this->dividendYield;
	}

	public function getdividendYieldDefaultCurrency(): Decimal
	{
		return $this->dividendYieldDefaultCurrency;
	}

	public function getdividendYieldPercentage(): float
	{
		return $this->dividendYieldPercentage;
	}

	public function getdividendYieldPercentagePerAnnum(): float
	{
		return $this->dividendYieldPercentagePerAnnum;
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

	public function getTaxDefaultCurrency(): Decimal
	{
		return $this->taxDefaultCurrency;
	}

	public function getFee(): Decimal
	{
		return $this->fee;
	}

	public function getFeeDefaultCurrency(): Decimal
	{
		return $this->feeDefaultCurrency;
	}

	public function getFirstTransactionActionCreated(): DateTimeImmutable
	{
		return $this->firstTransactionActionCreated;
	}

	/** @return list<string> */
	public function getBulkInsertColumns(): array
	{
		return [
			'user_id',
			'portfolio_id',
			'asset_id',
			'date',
			'price',
			'units',
			'value',
			'transaction_value',
			'transaction_value_default_currency',
			'average_price',
			'average_price_default_currency',
			'gain',
			'gain_default_currency',
			'gain_percentage',
			'gain_percentage_per_annum',
			'realized_gain',
			'realized_gain_default_currency',
			'dividend_yield',
			'dividend_yield_default_currency',
			'dividend_yield_percentage',
			'dividend_yield_percentage_per_annum',
			'fx_impact',
			'fx_impact_percentage',
			'fx_impact_percentage_per_annum',
			'return',
			'return_percentage',
			'return_percentage_per_annum',
			'tax',
			'tax_default_currency',
			'fee',
			'fee_default_currency',
			'first_transaction_action_created',
		];
	}

	/** @return list<string|int|float|DateTimeInterface> */
	public function getBulkInsertValues(): array
	{
		return [
			$this->user->getId(),
			$this->portfolio->getId(),
			$this->asset->getId(),
			$this->date,
			(string) $this->price,
			(string) $this->units,
			(string) $this->value,
			(string) $this->transactionValue,
			(string) $this->transactionValueDefaultCurrency,
			(string) $this->averagePrice,
			(string) $this->averagePriceDefaultCurrency,
			(string) $this->gain,
			(string) $this->gainDefaultCurrency,
			$this->gainPercentage,
			$this->gainPercentagePerAnnum,
			(string) $this->realizedGain,
			(string) $this->realizedGainDefaultCurrency,
			(string) $this->dividendYield,
			(string) $this->dividendYieldDefaultCurrency,
			$this->dividendYieldPercentage,
			$this->dividendYieldPercentagePerAnnum,
			(string) $this->fxImpact,
			(string) $this->fxImpactPercentage,
			$this->fxImpactPercentagePerAnnum,
			(string) $this->return,
			$this->returnPercentage,
			$this->returnPercentagePerAnnum,
			(string) $this->tax,
			(string) $this->taxDefaultCurrency,
			(string) $this->fee,
			(string) $this->feeDefaultCurrency,
			$this->firstTransactionActionCreated,
		];
	}
}
