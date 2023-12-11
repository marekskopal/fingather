<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\User;

/** @extends ARepository<User> */
class UserRepository extends ARepository
{
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
