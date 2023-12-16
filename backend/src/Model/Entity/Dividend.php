<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use DateTimeImmutable;
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
		private DateTimeImmutable $paidDate,
		#[Column(type: 'decimal(20,10)')]
		private string $priceGross,
		#[Column(type: 'decimal(20,10)')]
		private string $priceNet,
		#[Column(type: 'decimal(20,10)')]
		private string $tax,
		#[RefersTo(target: Currency::class)]
		private Currency $currency,
		#[Column(type: 'decimal(20,10)')]
		private string $exchangeRate,
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

	public function getPaidDate(): DateTimeImmutable
	{
		return $this->paidDate;
	}

	public function setPaidDate(DateTimeImmutable $paidDate): void
	{
		$this->paidDate = $paidDate;
	}

	public function getPriceGross(): string
	{
		return $this->priceGross;
	}

	public function setPriceGross(string $priceGross): void
	{
		$this->priceGross = $priceGross;
	}

	public function getPriceNet(): string
	{
		return $this->priceNet;
	}

	public function setPriceNet(string $priceNet): void
	{
		$this->priceNet = $priceNet;
	}

	public function getTax(): string
	{
		return $this->tax;
	}

	public function setTax(string $tax): void
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

	public function getExchangeRate(): string
	{
		return $this->exchangeRate;
	}

	public function setExchangeRate(string $exchangeRate): void
	{
		$this->exchangeRate = $exchangeRate;
	}
}
