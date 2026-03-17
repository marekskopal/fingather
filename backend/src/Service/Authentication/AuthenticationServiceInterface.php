<?php

declare(strict_types=1);

namespace FinGather\Service\Authentication;

use FinGather\Dto\AuthenticationDto;
use FinGather\Dto\CredentialsDto;
use FinGather\Model\Entity\User;

interface AuthenticationServiceInterface
{
	public const string TokenAlgorithm = 'HS256';

	public function authenticate(CredentialsDto $credential): AuthenticationDto;

	public function createAuthentication(User $user): AuthenticationDto;
}
