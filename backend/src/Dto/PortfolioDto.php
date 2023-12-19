<?php

declare(strict_types=1);

namespace FinGather\Dto;

final readonly class PortfolioDto
{
	/** @param list<GroupWithGroupDataDto> $groups */
	public function __construct(public array $groups, public PortfolioDataDto $portfolioData,)
	{
	}
}
