<?php

declare(strict_types=1);

namespace FinGather\Dto;

/**
 * @implements ArrayFactoryInterface<array{
 *     currencyId: int,
 *     name: string,
 *     isDefault: bool,
 * }>
 */
final readonly class PortfolioCreateDto implements ArrayFactoryInterface
{
	public function __construct(public int $currencyId, public string $name, public bool $isDefault)
	{
	}

	public static function fromArray(array $data): static
	{
		return new self(currencyId: $data['currencyId'], name: $data['name'], isDefault: $data['isDefault']);
	}
}
