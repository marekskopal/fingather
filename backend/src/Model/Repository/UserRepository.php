<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\User;

/** @extends ARepository<User> */
final class UserRepository extends ARepository
{
	/** @return list<User> */
	public function findUsers(): array
	{
		return $this->findAll();
	}

	public function findUserById(int $userId): ?User
	{
		return $this->findOne([
			'id' => $userId,
		]);
	}

	public function findUserByEmail(string $email): ?User
	{
		return $this->findOne([
			'email' => $email,
		]);
	}
}
