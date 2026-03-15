<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Enum\LocaleEnum;
use SensitiveParameter;

/**
 * @implements ArrayFactoryInterface<array{
 *     idToken: string,
 *     defaultCurrencyId?: int|null,
 *     locale?: value-of<LocaleEnum>,
 * }>
 */
final readonly class GoogleLoginDto implements ArrayFactoryInterface
{
	public function __construct(
		#[SensitiveParameter] public string $idToken,
		public ?int $defaultCurrencyId = null,
		public LocaleEnum $locale = LocaleEnum::En,
	) {
	}

	public static function fromArray(array $data): static
	{
		return new self(
			idToken: $data['idToken'],
			defaultCurrencyId: $data['defaultCurrencyId'] ?? null,
			locale: LocaleEnum::tryFrom($data['locale'] ?? '') ?? LocaleEnum::En,
		);
	}
}
