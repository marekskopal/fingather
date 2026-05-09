<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Decimal\Decimal;
use FinGather\Model\Entity\Enum\CostBasisMethodEnum;
use FinGather\Model\Entity\Enum\TaxJurisdictionEnum;
use FinGather\Model\Repository\PortfolioRepository;
use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Attribute\ColumnEnum;
use MarekSkopal\ORM\Attribute\Entity;
use MarekSkopal\ORM\Attribute\ManyToOne;
use MarekSkopal\ORM\Decimal\Attribute\ColumnDecimal;
use MarekSkopal\ORM\Enum\Type;

#[Entity(repositoryClass: PortfolioRepository::class)]
class Portfolio extends AEntity
{
	public function __construct(
		#[ManyToOne(entityClass: User::class)]
		public readonly User $user,
		#[ManyToOne(entityClass: Currency::class)]
		public Currency $currency,
		#[Column(type: Type::String)]
		public string $name,
		#[Column(type: Type::Boolean)]
		public bool $isDefault,
		#[ColumnEnum(enum: TaxJurisdictionEnum::class, default: TaxJurisdictionEnum::Generic)]
		public TaxJurisdictionEnum $taxJurisdiction = TaxJurisdictionEnum::Generic,
		#[ColumnEnum(enum: CostBasisMethodEnum::class, default: CostBasisMethodEnum::Fifo)]
		public CostBasisMethodEnum $costBasisMethod = CostBasisMethodEnum::Fifo,
		#[ColumnDecimal(precision: 5, scale: 4, nullable: true)]
		public ?Decimal $estimatedTaxRate = null,
	) {
	}
}
