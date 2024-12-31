<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\EmailVerify;
use MarekSkopal\ORM\Repository\AbstractRepository;

/** @extends AbstractRepository<EmailVerify> */
final class EmailVerifyRepository extends AbstractRepository
{
	public function findEmailVerifyByToken(string $token): ?EmailVerify
	{
		return $this->findOne([
			'token' => $token,
		]);
	}
}
