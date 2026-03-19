<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\PasswordReset;
use FinGather\Model\Entity\User;

interface PasswordResetProviderInterface
{
	public function getPasswordReset(string $token): ?PasswordReset;

	public function createPasswordReset(User $user): PasswordReset;

	public function deletePasswordReset(PasswordReset $passwordReset): void;
}
