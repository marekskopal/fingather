<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\User;
use Iterator;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<User> */
final class UserRepository extends AbstractRepository
{
	/** @return Iterator<User> */
	public function findUsers(): Iterator
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

	public function findUserByGoogleId(string $googleId): ?User
	{
		return $this->findOne([
			'google_id' => $googleId,
		]);
	}
}
