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
		#[RefersTo(target: Portfolio::class)]
		private Portfolio $portfolio,
		#[RefersTo(target: Asset::class)]
		private Asset $asset,
		#[RefersTo(target: Broker::class)]
		private Broker $broker,
		#[Column(type: 'enum(Undefined,Buy,Sell,Dividend)')]
		private string $actionType,
		#[Column(type: 'timestamp')]
		private DateTimeImmutable $actionCreated,
		#[Column(type: 'enum(Manual,Import)', default: 'Manual')]
		private string $createType,
		#[Column(type: 'timestamp')]
		private DateTimeImmutable $created,
		#[Column(type: 'timestamp')]
		private DateTimeImmutable $modified,
		#[Column(type: 'decimal(18,8)')]
		private string $units,
		#[Column(type: 'decimal(9,2)')]
		private string $price,
		#[RefersTo(target: Currency::class)]
		private Currency $currency,
		#[Column(type: 'decimal(9,2)')]
		private string $tax,
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

	public function getActionCreated(): DateTimeImmutable
	{
		return $this->actionCreated;
	}

	public function setActionCreated(DateTimeImmutable $actionCreated): void
	{
		$this->actionCreated = $actionCreated;
	}

	public function getCreateType(): string
	{
		return $this->createType;
	}

	public function setCreateType(string $createType): void
	{
		$this->createType = $createType;
	}

	public function getCreated(): DateTimeImmutable
	{
		return $this->created;
	}

	public function setCreated(DateTimeImmutable $created): void
	{
		$this->created = $created;
	}

	public function getModified(): DateTimeImmutable
	{
		return $this->modified;
	}

	public function setModified(DateTimeImmutable $modified): void
	{
		$this->modified = $modified;
	}

	public function getUnits(): string
	{
		return $this->units;
	}

	public function setUnits(string $units): void
	{
		$this->units = $units;
	}

	public function getPrice(): string
	{
		return $this->price;
	}

	public function setPrice(string $price): void
	{
		$this->price = $price;
	}

	public function getCurrency(): Currency
	{
		return $this->currency;
	}

	public function setCurrency(Currency $currency): void
	{
		$this->currency = $currency;
	}

	public function getTax(): string
	{
		return $this->tax;
	}

	public function setTax(string $tax): void
	{
		$this->tax = $tax;
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
