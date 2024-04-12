<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\UserRoleEnum;
use FinGather\Model\Entity\User;

class UserFixture
{
	public static function getUser(
		?string $email = null,
		?string $password = null,
		?string $name = null,
		?Currency $defaultCurrency = null,
		?UserRoleEnum $role = null,
		?bool $isEmailVerified = null,
	): User
	{
		return new User(
			email: $email ?? 'test@fingather.com',
			password: $password ?? '$2y$10$1q2w3e4r5t6y7u8i9o0p1q2w3e4r5t6y7u8i9o0p1q2w3e4r5t6y7u8i9o0p',
			name: $name ?? 'Test User',
			defaultCurrency: $defaultCurrency ?? CurrencyFixture::getCurrency(),
			role: $role ?? UserRoleEnum::Admin,
			isEmailVerified: $isEmailVerified ?? false,
		);
	}
}
