<?php

declare(strict_types=1);

namespace FinGather\Dto;

readonly class GroupDto
{
	public function __construct(public int $id, public int $userId, public string $name,)
	{
	}
}
