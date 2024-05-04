<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Currency;

final readonly class CurrencyDto
{
	public function __construct(public int $id, public string $code, public string $name, public string $symbol, public bool $isSelectable)
	{
	}

	public static function fromEntity(Currency $entity): self
	{
		return new self(
			id: $entity->getId(),
			code: $entity->getCode(),
			name: $entity->getName(),
			symbol: $entity->getSymbol(),
			isSelectable: $entity->getIsSelectable(),
		);
	}
}
