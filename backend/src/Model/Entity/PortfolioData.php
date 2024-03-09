<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Entity;
use Cycle\ORM\Parser\Typecast;
use FinGather\Model\Repository\PortfolioDataRepository;
use MarekSkopal\Cycle\Decimal\DecimalTypecast;

#[Entity(repository: PortfolioDataRepository::class, typecast: [
	Typecast::class,
	DecimalTypecast::class,
])]
class PortfolioData extends ADataEntity
{
}
