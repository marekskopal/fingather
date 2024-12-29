<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use MarekSkopal\ORM\Attribute\Column;
use MarekSkopal\ORM\Enum\Type;

abstract class AEntity
{
	#[Column(type: Type::Int, primary: true, autoIncrement: true)]

	// @phpstan-ignore-next-line
	public int $id;
}
