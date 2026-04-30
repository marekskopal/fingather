<?php

declare(strict_types=1);

namespace FinGather\Dto;

final readonly class ImpersonationAuthenticationDto
{
	public function __construct(
		public string $accessToken,
		public int $expiresAt,
		public int $sessionId,
		public int $targetUserId,
		public string $targetUserEmail,
		public string $targetUserName,
	) {
	}
}
