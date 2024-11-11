<?php

declare(strict_types=1);

namespace FinGather\Command;

abstract class AbstractBenchmarkCommand extends AbstractCommand
{
	/** @param callable(): mixed $callback */
	protected function benchmark(callable $callback): int
	{
		$timeStart = hrtime(true);

		$callback();

		$timeEnd = hrtime(true);

		return (int) ceil(($timeEnd - $timeStart) / 1000000);
	}
}
