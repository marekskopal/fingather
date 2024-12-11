<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use MarekSkopal\ORM\Attribute\Column;

abstract class AEntity
{
	#[Column(type: 'int', primary: true)]

	// @phpstan-ignore-next-line
	public int $id;

	public function getId(): int
	{
		return $this->id;
	}
}
