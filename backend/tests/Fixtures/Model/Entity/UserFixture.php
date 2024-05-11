<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use FinGather\Model\Entity\Enum\UserRoleEnum;
use FinGather\Model\Entity\User;

final class UserFixture
{
	public static function getUser(
		?string $email = null,
		?string $password = null,
		?string $name = null,
		?UserRoleEnum $role = null,
		?bool $isEmailVerified = null,
		?bool $isOnboardingCompleted = null,
	): User {
		return new User(
			email: $email ?? 'test@fingather.com',
			password: $password ?? '$2y$10$1q2w3e4r5t6y7u8i9o0p1q2w3e4r5t6y7u8i9o0p1q2w3e4r5t6y7u8i9o0p',
			name: $name ?? 'Test User',
			role: $role ?? UserRoleEnum::Admin,
			isEmailVerified: $isEmailVerified ?? false,
			isOnboardingCompleted: $isOnboardingCompleted ?? false,
		);
	}
}
