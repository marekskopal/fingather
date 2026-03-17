<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator\Dto;

use Decimal\Decimal;

final class TransactionAccumulatorDto
{
	/** @var array<int, TransactionBuyDto> */
	public array $buys = [];

	public Decimal $units;

	public Decimal $realizedGain;

	public Decimal $realizedGainDefaultCurrency;

	public Decimal $dividendYield;

	public Decimal $dividendYieldDefaultCurrency;

	public Decimal $dividendYieldTickerCurrency;

	public Decimal $tax;

	public Decimal $taxDefaultCurrency;

	public Decimal $fee;

	public Decimal $feeDefaultCurrency;

	public function __construct()
	{
		$this->units = new Decimal(0);
		$this->realizedGain = new Decimal(0);
		$this->realizedGainDefaultCurrency = new Decimal(0);
		$this->dividendYield = new Decimal(0);
		$this->dividendYieldDefaultCurrency = new Decimal(0);
		$this->dividendYieldTickerCurrency = new Decimal(0);
		$this->tax = new Decimal(0);
		$this->taxDefaultCurrency = new Decimal(0);
		$this->fee = new Decimal(0);
		$this->feeDefaultCurrency = new Decimal(0);
	}
}
