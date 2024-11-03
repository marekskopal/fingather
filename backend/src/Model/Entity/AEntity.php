<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;

abstract class AEntity
{
	#[Column(type: 'primary')]

	// @phpstan-ignore-next-line
	protected int $id;

	public function getId(): int
	{
		return $this->id;
	}
}
