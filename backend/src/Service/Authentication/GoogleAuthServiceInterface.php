<?php

declare(strict_types=1);

namespace FinGather\Service\Authentication;

use FinGather\Service\Authentication\Dto\TokenInfoDto;

interface GoogleAuthServiceInterface
{
	public function verifyIdToken(string $idToken): TokenInfoDto;
}
