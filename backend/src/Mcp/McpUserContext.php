<?php

declare(strict_types=1);

namespace FinGather\Mcp;

use FinGather\Model\Entity\User;
use RuntimeException;

final class McpUserContext implements McpUserContextInterface
{
	private ?User $user = null;

	public function setUser(User $user): void
	{
		$this->user = $user;
	}

	public function getUser(): User
	{
		return $this->user ?? throw new RuntimeException('MCP user context has not been initialized.');
	}
}
