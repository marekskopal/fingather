<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\HasOne;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use FinGather\Model\Entity\Enum\ActionTypeEnum;
use FinGather\Model\Repository\BrokerRepository;
use FinGather\Model\Repository\CurrencyRepository;
use FinGather\Model\Repository\DividendRepository;

#[Entity(repository: DividendRepository::class)]
class Dividend extends AEntity
{
	public function __construct(
		#[RefersTo(target: Asset::class)]
		private Asset $asset,
		#[RefersTo(target: Broker::class)]
		private Broker $broker,
		#[Column(type: 'timestamp')]
		private \DateTime $paidDate,
		#[Column(type: 'decimal(10,10)')]
		private float $priceGross,
		#[Column(type: 'decimal(10,10)')]
		private float $priceNet,
		#[Column(type: 'decimal(10,10)')]
		private float $tax,
		#[RefersTo(target: Currency::class)]
		private Currency $currency,
		#[Column(type: 'decimal(10,10)')]
		private float $exchangeRate,
	) {
	}

	public function getAsset(): Asset
	{
		return $this->asset;
	}

	public function setAsset(Asset $asset): void
	{
		$this->asset = $asset;
	}

	public function getBroker(): Broker
	{
		return $this->broker;
	}

	public function setBroker(Broker $broker): void
	{
		$this->broker = $broker;
	}

	public function getPaidDate(): \DateTime
	{
		return $this->paidDate;
	}

	public function setPaidDate(\DateTime $paidDate): void
	{
		$this->paidDate = $paidDate;
	}

	public function getPriceGross(): float
	{
		return $this->priceGross;
	}

	public function setPriceGross(float $priceGross): void
	{
		$this->priceGross = $priceGross;
	}

	public function getPriceNet(): float
	{
		return $this->priceNet;
	}

	public function setPriceNet(float $priceNet): void
	{
		$this->priceNet = $priceNet;
	}

	public function getTax(): float
	{
		return $this->tax;
	}

	public function setTax(float $tax): void
	{
		$this->tax = $tax;
	}

	public function getCurrency(): Currency
	{
		return $this->currency;
	}

	public function setCurrency(Currency $currency): void
	{
		$this->currency = $currency;
	}

	public function getExchangeRate(): float
	{
		return $this->exchangeRate;
	}

	public function setExchangeRate(float $exchangeRate): void
	{
		$this->exchangeRate = $exchangeRate;
	}
}
