<?php

declare(strict_types=1);

namespace FinGather\App;

use FinGather\App\Dispatcher\Dispatcher;
use FinGather\App\Dispatcher\HttpDispatcher;
use FinGather\App\Dispatcher\JobsDispatcher;
use Spiral\RoadRunner\Environment;

final class RoadRunnerWorker
{
	public function run(): void
	{
		/** @var list<Dispatcher> $dispatchers */
		$dispatchers = [
			new HttpDispatcher(),
			new JobsDispatcher(),
		];

		$env = Environment::fromGlobals();

		foreach ($dispatchers as $dispatcher) {
			if (!$dispatcher->canServe($env)) {
				continue;
			}

			$dispatcher->serve();
		}
	}
}
