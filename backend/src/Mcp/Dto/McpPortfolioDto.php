<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

use FinGather\Model\Entity\Portfolio;

final readonly class McpPortfolioDto
{
	public function __construct(public int $id, public string $name, public string $currency, public bool $isDefault,)
	{
	}

	public static function fromEntity(Portfolio $portfolio): self
	{
		return new self(id: $portfolio->id, name: $portfolio->name, currency: $portfolio->currency->code, isDefault: $portfolio->isDefault);
	}
}
