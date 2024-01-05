<?php

declare(strict_types=1);

namespace FinGather\Dto;

final readonly class TransactionListDto
{
	/** @param list<TransactionDto> $transactions */
	public function __construct(public array $transactions, public int $count,)
	{
	}
}
