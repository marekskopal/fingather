<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Enum\TransactionCreateTypeEnum;
use FinGather\Model\Repository\TransactionRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\ColumnEnum;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ForeignKey;
use MarekSkopal\ORM\Attribute\ManyToOne;
use MarekSkopal\ORM\Decimal\Attribute\ColumnDecimal;
use MarekSkopal\ORM\Enum\Type;

#[Entity(repositoryClass: TransactionRepository::class)]
class Transaction extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: User::class)]
		public readonly User $user,
		#[ManyToOne(entityClass: Portfolio::class)]
		public readonly Portfolio $portfolio,
		#[ManyToOne(entityClass: Asset::class)]
		public Asset $asset,
		#[Column(type: Type::Int, nullable: true)]
		#[ForeignKey(entityClass: Broker::class)]
		public ?int $brokerId,
		#[ColumnEnum(enum: TransactionActionTypeEnum::class)]
		public TransactionActionTypeEnum $actionType,
		#[Column(type: Type::Timestamp)]
		public DateTimeImmutable $actionCreated,
		#[ColumnEnum(
			enum: TransactionCreateTypeEnum::class,
			default: TransactionCreateTypeEnum::Manual->value,
		)]
		public TransactionCreateTypeEnum $createType,
		#[Column(type: Type::Timestamp)]
		public DateTimeImmutable $created,
		#[Column(type: Type::Timestamp)]
		public DateTimeImmutable $modified,
		#[ColumnDecimal(precision: 18, scale: 8)]
		public Decimal $units,
		#[ColumnDecimal(precision: 11, scale: 2)]
		public Decimal $price,
		#[ManyToOne(entityClass: Currency::class)]
		public Currency $currency,
		#[ColumnDecimal(precision: 11, scale: 2)]
		public Decimal $priceTickerCurrency,
		#[ColumnDecimal(precision: 11, scale: 2)]
		public Decimal $priceDefaultCurrency,
		#[ColumnDecimal(precision: 11, scale: 2)]
		public Decimal $tax,
		#[ManyToOne(entityClass: Currency::class)]
		public Currency $taxCurrency,
		#[ColumnDecimal(precision: 11, scale: 2)]
		public Decimal $taxTickerCurrency,
		#[ColumnDecimal(precision: 11, scale: 2)]
		public Decimal $taxDefaultCurrency,
		#[ColumnDecimal(precision: 11, scale: 2)]
		public Decimal $fee,
		#[ManyToOne(entityClass: Currency::class)]
		public Currency $feeCurrency,
		#[ColumnDecimal(precision: 11, scale: 2)]
		public Decimal $feeTickerCurrency,
		#[ColumnDecimal(precision: 11, scale: 2)]
		public Decimal $feeDefaultCurrency,
		#[Column(type: Type::TinyText, nullable: true)]
		public ?string $notes,
		#[Column(type: Type::String, nullable: true)]
		public ?string $importIdentifier,
	) {
	}
}
