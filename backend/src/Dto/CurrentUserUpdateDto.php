<?php

declare(strict_types=1);

namespace FinGather\Dto;

/**
 * @implements ArrayFactoryInterface<array{
 *     isEmailNotificationsEnabled: bool,
 * }>
 */
final readonly class CurrentUserUpdateDto implements ArrayFactoryInterface
{
	public function __construct(public bool $isEmailNotificationsEnabled)
	{
	}

	public static function fromArray(array $data): static
	{
		return new self(isEmailNotificationsEnabled: $data['isEmailNotificationsEnabled']);
	}
}
