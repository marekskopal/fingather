<?php

declare(strict_types=1);

namespace FinGather\Dto;

/**
 * @implements ArrayFactoryInterface<array{
 *     name: string,
 *     email: string,
 *     password: string,
 *     isEmailNotificationsEnabled: bool,
 * }>
 */
final readonly class CurrentUserUpdateDto implements ArrayFactoryInterface
{
	public function __construct(
		public string $name,
		public string $email,
		public string $password,
		public bool $isEmailNotificationsEnabled,
	) {
	}

	public static function fromArray(array $data): static
	{
		return new self(
			name: $data['name'],
			email: $data['email'],
			password: $data['password'],
			isEmailNotificationsEnabled: $data['isEmailNotificationsEnabled'],
		);
	}
}
