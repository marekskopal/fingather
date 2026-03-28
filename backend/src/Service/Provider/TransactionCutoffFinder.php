<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Transaction;

final readonly class TransactionCutoffFinder
{
	/**
	 * Binary search for the first transaction index with actionCreated > $beforeDate.
	 * Returns the number of transactions with actionCreated <= $beforeDate.
	 *
	 * @param list<Transaction> $transactions Sorted by actionCreated ASC
	 */
	public function findCutoffIndex(array $transactions, DateTimeImmutable $beforeDate): int
	{
		$low = 0;
		$high = count($transactions);

		while ($low < $high) {
			$mid = intdiv($low + $high, 2);
			if ($transactions[$mid]->actionCreated <= $beforeDate) {
				$low = $mid + 1;
			} else {
				$high = $mid;
			}
		}

		return $low;
	}
}
