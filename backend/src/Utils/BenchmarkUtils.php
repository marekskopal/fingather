<?php

declare(strict_types=1);

namespace FinGather\Utils;

final class BenchmarkUtils
{
	/** @param callable(): mixed $callback */
	public static function benchmark(callable $callback): int
	{
		$timeStart = hrtime(true);

		$callback();

		$timeEnd = hrtime(true);

		return (int) ceil(($timeEnd - $timeStart) / 1000000);
	}
}
