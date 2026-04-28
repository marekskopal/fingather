<?php

declare(strict_types=1);

namespace FinGather\Mcp\Dto;

final readonly class McpDividendDataDto
{
	/**
	 * @param list<McpDividendIntervalDto> $history
	 * @param list<McpDividendCalendarItemDto> $calendar
	 */
	public function __construct(public int $portfolioId, public string $currency, public array $history, public array $calendar,)
	{
	}
}
