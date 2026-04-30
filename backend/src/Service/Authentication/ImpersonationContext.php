<?php

declare(strict_types=1);

namespace FinGather\Service\Authentication;

/** @api */
final class ImpersonationContext
{
	private ?int $impersonatorId = null;

	private ?int $sessionId = null;

	public function activate(int $impersonatorId, int $sessionId): void
	{
		$this->impersonatorId = $impersonatorId;
		$this->sessionId = $sessionId;
	}

	public function deactivate(): void
	{
		$this->impersonatorId = null;
		$this->sessionId = null;
	}

	public function isImpersonating(): bool
	{
		return $this->impersonatorId !== null;
	}

	public function getImpersonatorId(): ?int
	{
		return $this->impersonatorId;
	}

	public function getSessionId(): ?int
	{
		return $this->sessionId;
	}
}
