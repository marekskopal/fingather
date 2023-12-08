<?php

namespace FinGather\Model\Repository;

use Cycle\ORM\Select\Repository;
use FinGather\Model\Entity\User;

/** @extends Repository<User> */
class UserRepository extends Repository
{
	public function findUserByEmail(string $email): ?User
	{
		return $this->findOne([
			'email' => $email,
		]);
	}
}