<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use DateTimeImmutable;
use FinGather\Model\Repository\TransactionRepository;

#[Entity(repository: TransactionRepository::class)]
class Transaction extends AEntity
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
		#[Column(type: 'decimal(20,10)')]
		private string $units,
		#[Column(type: 'decimal(20,10)')]
		private string $priceUnit,
		#[RefersTo(target: Currency::class)]
		private Currency $currency,
		#[Column(type: 'decimal(20,10)')]
		private string $exchangeRate,
		#[Column(type: 'decimal(20,10)')]
		private string $feeConversion,
		#[Column(type: 'tinyText', nullable: true)]
		private ?string $notes,
		#[Column(type: 'string', nullable: true)]
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

	public function getUnits(): string
	{
		return $this->units;
	}

	public function setUnits(string $units): void
	{
		$this->units = $units;
	}

	public function getPriceUnit(): string
	{
		return $this->priceUnit;
	}

	public function setPriceUnit(string $priceUnit): void
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

	public function getExchangeRate(): string
	{
		return $this->exchangeRate;
	}

	public function setExchangeRate(string $exchangeRate): void
	{
		$this->exchangeRate = $exchangeRate;
	}

	public function getFeeConversion(): string
	{
		return $this->feeConversion;
	}

	public function setFeeConversion(string $feeConversion): void
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
