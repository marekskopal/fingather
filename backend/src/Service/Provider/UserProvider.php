<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\UserRoleEnum;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\UserRepository;
use Iterator;
use SensitiveParameter;
use const PASSWORD_BCRYPT;

class UserProvider
{
	public function __construct(
		private readonly UserRepository $userRepository,
		private readonly EmailVerifyProvider $emailVerifyProvider,
		private readonly GroupProvider $groupProvider,
		private readonly PortfolioProvider $portfolioProvider,
	) {
	}

	/** @return Iterator<User> */
	public function getUsers(): Iterator
	{
		return $this->userRepository->findUsers();
	}

	public function getUser(int $userId): ?User
	{
		return $this->userRepository->findUserById($userId);
	}

	public function getUserByEmail(string $email): ?User
	{
		return $this->userRepository->findUserByEmail($email);
	}

	public function getUserByGoogleId(string $googleId): ?User
	{
		return $this->userRepository->findUserByGoogleId($googleId);
	}

	public function createUser(
		#[SensitiveParameter] string $email,
		#[SensitiveParameter] string $password,
		string $name,
		Currency $defaultCurrency,
		UserRoleEnum $role,
		bool $isEmailVerified,
	): User {
		$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

		$user = new User(
			email: $email,
			password: $hashedPassword,
			name: $name,
			role: $role,
			isEmailVerified: $isEmailVerified,
			isOnboardingCompleted: false,
			lastLoggedIn: null,
			lastRefreshTokenGenerated: null,
			googleId: null,
			isEmailNotificationsEnabled: true,
		);
		$this->userRepository->persist($user);

		$defaultPortfolio = $this->portfolioProvider->createDefaultPortfolio($user, $defaultCurrency);

		$this->groupProvider->createOthersGroup($user, $defaultPortfolio);

		if (!$isEmailVerified) {
			$this->emailVerifyProvider->createEmailVerify($user);
		}

		return $user;
	}

	public function createUserFromGoogle(
		#[SensitiveParameter] string $email,
		string $name,
		string $googleId,
		Currency $defaultCurrency,
	): User {
		$user = new User(
			email: $email,
			password: null,
			name: $name,
			role: UserRoleEnum::User,
			isEmailVerified: true,
			isOnboardingCompleted: false,
			lastLoggedIn: null,
			lastRefreshTokenGenerated: null,
			googleId: $googleId,
			isEmailNotificationsEnabled: true,
		);
		$this->userRepository->persist($user);

		$defaultPortfolio = $this->portfolioProvider->createDefaultPortfolio($user, $defaultCurrency);

		$this->groupProvider->createOthersGroup($user, $defaultPortfolio);

		return $user;
	}

	public function linkGoogleAccount(User $user, string $googleId): User
	{
		$user->googleId = $googleId;
		$this->userRepository->persist($user);

		return $user;
	}

	public function updateUser(User $user, string $email, #[SensitiveParameter] string $password, string $name, UserRoleEnum $role): User
	{
		if ($email !== $user->email) {
			$user->email = $email;
			$user->isEmailVerified = false;
			$this->emailVerifyProvider->createEmailVerify($user);
		}

		if ($password !== '') {
			$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
			$user->password = $hashedPassword;
		}

		$user->name = $name;
		$user->role = $role;

		$this->userRepository->persist($user);

		return $user;
	}

	public function emailVerifyUser(User $user): User
	{
		$user->isEmailVerified = true;

		$this->userRepository->persist($user);

		return $user;
	}

	public function onboardingCompleteUser(User $user): User
	{
		$user->isOnboardingCompleted = true;

		$this->userRepository->persist($user);

		return $user;
	}

	public function updateEmailNotifications(User $user, bool $isEmailNotificationsEnabled): User
	{
		$user->isEmailNotificationsEnabled = $isEmailNotificationsEnabled;
		$this->userRepository->persist($user);

		return $user;
	}

	public function deleteUser(User $user): void
	{
		$this->userRepository->delete($user);
	}

	public function updateLastLoggedIn(User $user): void
	{
		$user->lastLoggedIn = new DateTimeImmutable();
		$this->userRepository->persist($user);
	}

	public function updateLastRefreshTokenGenerated(User $user): void
	{
		$user->lastRefreshTokenGenerated = new DateTimeImmutable();
		$this->userRepository->persist($user);
	}
}
