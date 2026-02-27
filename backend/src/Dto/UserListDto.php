<?php

declare(strict_types=1);

namespace FinGather\Dto;

final readonly class UserListDto
{
	/** @param list<UserWithStatisticDto> $users */
	public function __construct(public array $users, public int $count,)
	{
	}
}
