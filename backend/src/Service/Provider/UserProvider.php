<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\UserRoleEnum;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\UserRepository;
use SensitiveParameter;
use const PASSWORD_BCRYPT;

class UserProvider
{
	public function __construct(
		private readonly UserRepository $userRepository,
		private readonly EmailVerifyProvider $emailVerifyProvider,
		private readonly GroupProvider $groupProvider,
		private readonly PortfolioProvider $portfolioProvider,
		private readonly DataProvider $dataProvider,
	) {
	}

	/** @return iterable<User> */
	public function getUsers(): iterable
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
			defaultCurrency: $defaultCurrency,
			role: $role,
			isEmailVerified: $isEmailVerified,
		);
		$this->userRepository->persist($user);

		$defaultPortfolio = $this->portfolioProvider->createDefaultPortfolio($user);

		$this->groupProvider->createOthersGroup($user, $defaultPortfolio);

		if (!$isEmailVerified) {
			$this->emailVerifyProvider->createEmailVerify($user);
		}

		return $user;
	}

	public function updateUser(
		User $user,
		#[SensitiveParameter] string $password,
		string $name,
		Currency $defaultCurrency,
		UserRoleEnum $role,
	): User {
		if ($password !== '') {
			$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
			$user->setPassword($hashedPassword);
		}

		if ($defaultCurrency->getId() !== $user->getDefaultCurrency()->getId()) {
			$this->dataProvider->deleteUserData(user: $user, recalculateTransactions: true);
		}

		$user->setName($name);
		$user->setDefaultCurrency($defaultCurrency);
		$user->setRole($role);
		$this->userRepository->persist($user);

		return $user;
	}

	public function emailVerifyUser(User $user): User
	{
		$user->setIsEmailVerified(true);

		$this->userRepository->persist($user);

		return $user;
	}

	public function deleteUser(User $user): void
	{
		$this->userRepository->delete($user);
	}
}
