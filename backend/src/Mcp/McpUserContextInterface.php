<?php

declare(strict_types=1);

namespace FinGather\Mcp;

use FinGather\Model\Entity\User;

interface McpUserContextInterface
{
	public function setUser(User $user): void;

	public function getUser(): User;
}
