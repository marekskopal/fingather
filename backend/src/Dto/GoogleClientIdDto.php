<?php

declare(strict_types=1);

namespace FinGather\Dto;

final readonly class GoogleClientIdDto
{
	public function __construct(public string $googleClientId)
	{
	}
}
