<?php

declare(strict_types=1);

namespace FinGather\Utils;

final class BatchUtils
{
	/**
	 * @param list<T> $items
	 * @param int $batchSize
	 * @param callable(list<T>): void $callback
	 * @template T
	 */
	public static function batchCall(array $items, int $batchSize, callable $callback): void
	{
		$i = 0;
		$remains = count($items);
		$batchItems = [];
		foreach ($items as $item) {
			$i++;
			$batchItems[] = $item;

			$batchSize = min($batchSize, $remains);
			if ($i !== $batchSize) {
				continue;
			}

			$callback($batchItems);

			$i = 0;
			$batchItems = [];

			$remains -= $batchSize;
		}
	}
}
