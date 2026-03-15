<?php

declare(strict_types=1);

namespace FinGather\Dto;

use FinGather\Model\Entity\Enum\LocaleEnum;

/**
 * @implements ArrayFactoryInterface<array{
 *     locale: value-of<LocaleEnum>,
 * }>
 */
final readonly class CurrentUserLocaleDto implements ArrayFactoryInterface
{
	public function __construct(public LocaleEnum $locale)
	{
	}

	public static function fromArray(array $data): static
	{
		return new self(locale: LocaleEnum::from($data['locale']));
	}
}
