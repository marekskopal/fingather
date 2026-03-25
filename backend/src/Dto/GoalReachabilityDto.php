<?php

declare(strict_types=1);

namespace FinGather\Dto;

final readonly class GoalReachabilityDto
{
	public function __construct(public ?bool $isReachable, public ?string $projectedAchievementDate,)
	{
	}
}
