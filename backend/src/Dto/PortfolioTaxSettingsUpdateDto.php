<?php

declare(strict_types=1);

namespace FinGather\Dto;

/**
 * @implements ArrayFactoryInterface<array{
 *     taxJurisdiction: string,
 *     costBasisMethod: string,
 *     estimatedTaxRate?: string|null,
 * }>
 */
final readonly class PortfolioTaxSettingsUpdateDto implements ArrayFactoryInterface
{
	public function __construct(public string $taxJurisdiction, public string $costBasisMethod, public ?string $estimatedTaxRate,)
	{
	}

	public static function fromArray(array $data): static
	{
		return new self(
			taxJurisdiction: $data['taxJurisdiction'],
			costBasisMethod: $data['costBasisMethod'],
			estimatedTaxRate: $data['estimatedTaxRate'] ?? null,
		);
	}
}
