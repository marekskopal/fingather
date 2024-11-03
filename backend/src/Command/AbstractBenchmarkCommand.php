<?php

declare(strict_types=1);

namespace FinGather\Command;

use function Safe\hrtime;

abstract class AbstractBenchmarkCommand extends AbstractCommand
{
	/** @param callable(): mixed $callback */
	protected function benchmark(callable $callback): int
	{
		$timeStart = hrtime(true);

		$callback();

		$timeEnd = hrtime(true);

		//@phpstan-ignore-next-line
		return (int) ceil(($timeEnd - $timeStart) / 1000000);
	}
}
