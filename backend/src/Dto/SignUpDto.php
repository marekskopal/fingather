<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Enum\LocaleEnum;
use SensitiveParameter;

/**
 * @implements ArrayFactoryInterface<array{
 *     email: string,
 *     name: string,
 *     password: string,
 *     defaultCurrencyId: int,
 *     locale?: value-of<LocaleEnum>,
 * }>
 */
final readonly class SignUpDto implements ArrayFactoryInterface
{
	public function __construct(
		#[SensitiveParameter] public string $email,
		#[SensitiveParameter] public string $password,
		public string $name,
		public int $defaultCurrencyId,
		public LocaleEnum $locale = LocaleEnum::En,
	) {
	}

	public static function fromArray(array $data): static
	{
		return new self(
			email: $data['email'],
			name: $data['name'],
			password: $data['password'],
			defaultCurrencyId: $data['defaultCurrencyId'],
			locale: LocaleEnum::tryFrom($data['locale'] ?? '') ?? LocaleEnum::En,
		);
	}
}
