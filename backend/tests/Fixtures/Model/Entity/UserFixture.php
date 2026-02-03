<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use DateTimeImmutable;
use FinGather\Model\Entity\Enum\UserRoleEnum;
use FinGather\Model\Entity\User;

final class UserFixture
{
	public static function getUser(
		?int $id = null,
		?string $email = null,
		?string $password = null,
		?string $name = null,
		?UserRoleEnum $role = null,
		?bool $isEmailVerified = null,
		?bool $isOnboardingCompleted = null,
		?DateTimeImmutable $lastLoggedIn = null,
		?DateTimeImmutable $lastRefreshTokenGenerated = null,
		?string $googleId = null,
		?bool $isEmailNotificationsEnabled = null,
	): User {
		$user = new User(
			email: $email ?? 'test@fingather.com',
			password: $password ?? '$2y$10$1q2w3e4r5t6y7u8i9o0p1q2w3e4r5t6y7u8i9o0p1q2w3e4r5t6y7u8i9o0p',
			name: $name ?? 'Test User',
			role: $role ?? UserRoleEnum::Admin,
			isEmailVerified: $isEmailVerified ?? false,
			isOnboardingCompleted: $isOnboardingCompleted ?? false,
			lastLoggedIn: $lastLoggedIn,
			lastRefreshTokenGenerated: $lastRefreshTokenGenerated,
			googleId: $googleId,
			isEmailNotificationsEnabled: $isEmailNotificationsEnabled ?? false,
		);

		$user->id = $id ?? 1;

		return $user;
	}
}
