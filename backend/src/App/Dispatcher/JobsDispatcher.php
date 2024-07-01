<?php

declare(strict_types=1);

namespace FinGather\App\Dispatcher;

use FinGather\App\ApplicationFactory;
use FinGather\Jobs\Handler\EmailVerifyHandler;
use FinGather\Jobs\Handler\JobHandler;
use Spiral\RoadRunner\Environment\Mode;
use Spiral\RoadRunner\EnvironmentInterface;
use Spiral\RoadRunner\Jobs\Consumer;

final class JobsDispatcher implements Dispatcher
{
	public function canServe(EnvironmentInterface $env): bool
	{
		return $env->getMode() === Mode::MODE_JOBS;
	}

	public function serve(): void
	{
		$consumer = new Consumer();

		$application = ApplicationFactory::create();

		while ($task = $consumer->waitTask()) {
			try {
				$handlerClass = match ($task->getQueue()) {
					'email-verify' => EmailVerifyHandler::class,
					default => throw new \InvalidArgumentException('Unprocessable queue [' . $task->getQueue() . ']'),
				};

				/** @var JobHandler $handler */
				$handler = $application->container->get($handlerClass);
				$handler->handle($task);

				$task->ack();

				gc_collect_cycles();
			} catch (\Throwable $e) {
				$task->nack($e);
			}
		}
	}
}
