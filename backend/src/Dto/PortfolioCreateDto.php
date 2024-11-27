<?php

declare(strict_types=1);

namespace FinGather\Dto;

/**
 * @implements ArrayFactoryInterface<array{
 *     currency_id: int,
 *     name: string,
 *     is_default: bool,
 * }>
 */
final readonly class PortfolioCreateDto implements ArrayFactoryInterface
{
	public function __construct(public int $currencyId, public string $name, public bool $isDefault)
	{
	}

	public static function fromArray(array $data): static
	{
		return new self(currencyId: $data['currency_id'], name: $data['name'], isDefault: $data['is_default']);
	}
}
