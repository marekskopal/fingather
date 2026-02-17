<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\UserRoleEnum;
use FinGather\Model\Entity\User;
use Iterator;
use SensitiveParameter;

interface UserProviderInterface
{
	/** @return Iterator<User> */
	public function getUsers(): Iterator;

	public function getUser(int $userId): ?User;

	public function getUserByEmail(string $email): ?User;

	public function getUserByGoogleId(string $googleId): ?User;

	public function createUser(
		#[SensitiveParameter] string $email,
		#[SensitiveParameter] string $password,
		string $name,
		Currency $defaultCurrency,
		UserRoleEnum $role,
		bool $isEmailVerified,
	): User;

	public function createUserFromGoogle(
		#[SensitiveParameter] string $email,
		string $name,
		string $googleId,
		Currency $defaultCurrency,
	): User;

	public function linkGoogleAccount(User $user, string $googleId): User;

	public function updateUser(User $user, string $email, #[SensitiveParameter] string $password, string $name, UserRoleEnum $role): User;

	public function emailVerifyUser(User $user): User;

	public function onboardingCompleteUser(User $user): User;

	public function updateEmailNotifications(User $user, bool $isEmailNotificationsEnabled): User;

	public function deleteUser(User $user): void;

	public function updateLastLoggedIn(User $user): void;

	public function updateLastRefreshTokenGenerated(User $user): void;
}
