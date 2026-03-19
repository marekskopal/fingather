<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\EmailVerify;
use FinGather\Model\Entity\User;

interface EmailVerifyProviderInterface
{
	public function getEmailVerify(string $token): ?EmailVerify;

	public function createEmailVerify(User $user): EmailVerify;
}
