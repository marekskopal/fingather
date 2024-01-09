<?php

declare(strict_types=1);

namespace FinGather\Model\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\RefersTo;
use FinGather\Model\Repository\AssetRepository;

#[Entity(repository: AssetRepository::class)]
class EmailVerify extends AEntity
{
	public function __construct(#[RefersTo(target: User::class)] private User $user, #[Column(type: 'char(36)')] private string $token,)
	{
	}

	public function getUser(): User
	{
		return $this->user;
	}

	public function setUser(User $user): void
	{
		$this->user = $user;
	}

	public function getToken(): string
	{
		return $this->token;
	}

	public function setToken(string $token): void
	{
		$this->token = $token;
	}
}
