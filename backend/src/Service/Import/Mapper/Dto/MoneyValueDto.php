<?php

declare(strict_types=1);

namespace FinGather\Service\Import\Mapper\Dto;

final readonly class MoneyValueDto
{
	public function __construct(public ?string $value, public ?string $currency)
	{
	}
}
