<?php

declare(strict_types=1);

namespace FinGather\Service\Authentication;

use FinGather\Dto\AuthenticationDto;
use FinGather\Dto\CredentialsDto;
use FinGather\Dto\ImpersonationAuthenticationDto;
use FinGather\Model\Entity\User;

interface AuthenticationServiceInterface
{
	public const string TokenAlgorithm = 'HS256';

	public const string ClaimImpersonator = 'imp';
	public const string ClaimSessionId = 'sid';
	public const string ClaimType = 'typ';
	public const string TokenTypeImpersonation = 'imp';

	public function authenticate(CredentialsDto $credential): AuthenticationDto;

	public function createAuthentication(User $user): AuthenticationDto;

	public function createImpersonationAuthentication(User $admin, User $target, int $sessionId,): ImpersonationAuthenticationDto;

	public function getImpersonationTokenExpiration(): int;
}
