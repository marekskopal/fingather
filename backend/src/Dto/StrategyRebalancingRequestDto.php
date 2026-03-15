<?php

declare(strict_types=1);

namespace FinGather\Dto;

use Decimal\Decimal;

/**
 * @implements ArrayFactoryInterface<array{
 *     cashToInvest?: string,
 *     cashCurrencyId?: int,
 *     allowSelling?: bool,
 * }>
 */
final readonly class StrategyRebalancingRequestDto implements ArrayFactoryInterface
{
	public function __construct(public Decimal $cashToInvest, public ?int $cashCurrencyId, public bool $allowSelling,)
	{
	}

	public static function fromArray(array $data): static
	{
		return new self(
			cashToInvest: new Decimal($data['cashToInvest'] ?? '0'),
			cashCurrencyId: $data['cashCurrencyId'] ?? null,
			allowSelling: $data['allowSelling'] ?? false,
		);
	}
}
