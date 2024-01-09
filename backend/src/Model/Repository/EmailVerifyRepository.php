<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\EmailVerify;

/** @extends ARepository<EmailVerify> */
class EmailVerifyRepository extends ARepository
{
	public function findEmailVerifyByToken(string $token): ?EmailVerify
	{
		return $this->findOne([
			'token' => $token,
		]);
	}
}
