<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\User;
use FinGather\Model\Repository\Enum\OrderDirectionEnum;
use FinGather\Model\Repository\Enum\UserOrderByEnum;
use Iterator;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<User> */
final class UserRepository extends AbstractRepository
{
	/**
	 * @param array<value-of<UserOrderByEnum>,OrderDirectionEnum> $orderBy
	 * @return Iterator<User>
	 */
	public function findUsers(
		?int $limit = null,
		?int $offset = null,
		array $orderBy = [UserOrderByEnum::Id->value => OrderDirectionEnum::DESC],
	): Iterator {
		$select = $this->select();
		foreach ($orderBy as $column => $direction) {
			$select->orderBy($column, $direction->value);
		}
		return $select->limit($limit)->offset($offset)->fetchAll();
	}

	public function countUsers(): int
	{
		return $this->select()->count();
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
