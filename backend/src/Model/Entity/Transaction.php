<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use Cycle\ORM\Parser\Typecast;
use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Enum\TransactionCreateTypeEnum;
use FinGather\Model\Repository\TransactionRepository;
use MarekSkopal\Cycle\Decimal\DecimalTypecast;

#[Entity(repository: TransactionRepository::class, typecast: [
	Typecast::class,
	DecimalTypecast::class,
])]
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
		#[Column(type: 'enum(Undefined,Buy,Sell,Dividend)', typecast: TransactionActionTypeEnum::class)]
		private TransactionActionTypeEnum $actionType,
		#[Column(type: 'timestamp')]
		private DateTimeImmutable $actionCreated,
		#[Column(
			type: 'enum(Manual,Import)',
			default: TransactionCreateTypeEnum::Manual->value,
			typecast: TransactionCreateTypeEnum::class,
		)]
		private TransactionCreateTypeEnum $createType,
		#[Column(type: 'timestamp')]
		private DateTimeImmutable $created,
		#[Column(type: 'timestamp')]
		private DateTimeImmutable $modified,
		#[Column(type: 'decimal(18,8)', typecast: DecimalTypecast::Type)]
		private Decimal $units,
		#[Column(type: 'decimal(9,2)', typecast: DecimalTypecast::Type)]
		private Decimal $price,
		#[RefersTo(target: Currency::class)]
		private Currency $currency,
		#[Column(type: 'decimal(9,2)', typecast: DecimalTypecast::Type)]
		private Decimal $tax,
		#[RefersTo(target: Currency::class, innerKey:'tax_currency_id')]
		private Currency $taxCurrency,
		#[Column(type: 'decimal(9,2)', typecast: DecimalTypecast::Type)]
		private Decimal $fee,
		#[RefersTo(target: Currency::class, innerKey:'fee_currency_id')]
		private Currency $feeCurrency,
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

	public function getActionType(): TransactionActionTypeEnum
	{
		return $this->actionType;
	}

	public function setActionType(TransactionActionTypeEnum $actionType): void
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

	public function getCreateType(): TransactionCreateTypeEnum
	{
		return $this->createType;
	}

	public function setCreateType(TransactionCreateTypeEnum $createType): void
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

	public function getUnits(): Decimal
	{
		return $this->units;
	}

	public function setUnits(Decimal $units): void
	{
		$this->units = $units;
	}

	public function getPrice(): Decimal
	{
		return $this->price;
	}

	public function setPrice(Decimal $price): void
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

	public function getTax(): Decimal
	{
		return $this->tax;
	}

	public function setTax(Decimal $tax): void
	{
		$this->tax = $tax;
	}

	public function getTaxCurrency(): Currency
	{
		return $this->taxCurrency;
	}

	public function setTaxCurrency(Currency $taxCurrency): void
	{
		$this->taxCurrency = $taxCurrency;
	}

	public function getFee(): Decimal
	{
		return $this->fee;
	}

	public function setFee(Decimal $fee): void
	{
		$this->fee = $fee;
	}

	public function getFeeCurrency(): Currency
	{
		return $this->feeCurrency;
	}

	public function setFeeCurrency(Currency $feeCurrency): void
	{
		$this->feeCurrency = $feeCurrency;
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
