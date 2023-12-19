<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Entity;
use FinGather\Model\Repository\PortfolioDataRepository;

#[Entity(repository: PortfolioDataRepository::class)]
class PortfolioData extends ADataEntity
{
}
