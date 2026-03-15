<?php

declare(strict_types=1);

namespace FinGather\Dto;

/**
 * @implements ArrayFactoryInterface<array{
 *     currencyId?: int,
 *     name?: string,
 *     isDefault?: bool,
 * }>
 */
final readonly class PortfolioUpdateDto implements ArrayFactoryInterface
{
	public function __construct(public ?int $currencyId, public ?string $name, public ?bool $isDefault)
	{
	}

	public static function fromArray(array $data): static
	{
		return new self(currencyId: $data['currencyId'] ?? null, name: $data['name'] ?? null, isDefault: $data['isDefault'] ?? null);
	}
}
