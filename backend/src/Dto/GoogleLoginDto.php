<?php

declare(strict_types=1);

namespace FinGather\Dto;

use SensitiveParameter;

/**
 * @implements ArrayFactoryInterface<array{
 *     idToken: string,
 *     defaultCurrencyId?: int|null,
 * }>
 */
final readonly class GoogleLoginDto implements ArrayFactoryInterface
{
	public function __construct(#[SensitiveParameter] public string $idToken, public ?int $defaultCurrencyId = null,)
	{
	}

	public static function fromArray(array $data): static
	{
		return new self(idToken: $data['idToken'], defaultCurrencyId: $data['defaultCurrencyId'] ?? null);
	}
}
