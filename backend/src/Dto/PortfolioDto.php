<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Portfolio;

final readonly class PortfolioDto
{
	public function __construct(public int $id, public int $currencyId, public string $name, public bool $isDefault)
	{
	}

	public static function fromEntity(Portfolio $entity): self
	{
		return new self(id: $entity->id, currencyId: $entity->currency->id, name: $entity->name, isDefault: $entity->isDefault);
	}
}
