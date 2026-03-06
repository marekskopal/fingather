<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\PasswordReset;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<PasswordReset> */
final class PasswordResetRepository extends AbstractRepository
{
	public function findPasswordResetByToken(string $token): ?PasswordReset
	{
		return $this->findOne([
			'token' => $token,
		]);
	}
}
