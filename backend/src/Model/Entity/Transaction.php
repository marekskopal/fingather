<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use DateTimeImmutable;
use FinGather\Model\Repository\TransactionRepository;

#[Entity(repository: TransactionRepository::class)]
final class Transaction extends AEntity
{
	public function __construct(
		#[RefersTo(target: User::class)]
		private User $user,
		#[RefersTo(target: Asset::class)]
		private Asset $asset,
		#[RefersTo(target: Broker::class)]
		private Broker $broker,
		#[Column(type: 'enum(Undefined,Buy,Sell)')]
		private string $actionType,
		#[Column(type: 'timestamp')]
		private DateTimeImmutable $created,
		#[Column(type: 'decimal(10,10)')]
		private float $units,
		#[Column(type: 'decimal(10,10)')]
		private float $priceUnit,
		#[RefersTo(target: Currency::class)]
		private Currency $currency,
		#[Column(type: 'decimal(10,10)')]
		private float $exchangeRate,
		#[Column(type: 'decimal(10,10)')]
		private float $feeConversion,
		#[Column(type: 'tinyText')]
		private ?string $notes,
		#[Column(type: 'string')]
		private ?string $importIdentifier,
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

	public function getActionType(): string
	{
		return $this->actionType;
	}

	public function setActionType(string $actionType): void
	{
		$this->actionType = $actionType;
	}

	public function getCreated(): DateTimeImmutable
	{
		return $this->created;
	}

	public function setCreated(DateTimeImmutable $created): void
	{
		$this->created = $created;
	}

	public function getUnits(): float
	{
		return $this->units;
	}

	public function setUnits(float $units): void
	{
		$this->units = $units;
	}

	public function getPriceUnit(): float
	{
		return $this->priceUnit;
	}

	public function setPriceUnit(float $priceUnit): void
	{
		$this->priceUnit = $priceUnit;
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

	public function getFeeConversion(): float
	{
		return $this->feeConversion;
	}

	public function setFeeConversion(float $feeConversion): void
	{
		$this->feeConversion = $feeConversion;
	}

	public function getNotes(): ?string
	{
		return $this->notes;
	}

	public function setNotes(?string $notes): void
	{
		$this->notes = $notes;
	}

	public function getImportIdentifier(): ?string
	{
		return $this->importIdentifier;
	}

	public function setImportIdentifier(?string $importIdentifier): void
	{
		$this->importIdentifier = $importIdentifier;
	}
}
