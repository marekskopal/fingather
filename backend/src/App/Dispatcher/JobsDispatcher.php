<?php

declare(strict_types=1);

namespace FinGather\App\Dispatcher;

use FinGather\App\ApplicationFactory;
use FinGather\Jobs\Handler\ApiImportPrepareCheckHandler;
use FinGather\Jobs\Handler\ApiImportProcessCheckHandler;
use FinGather\Jobs\Handler\EmailVerifyHandler;
use FinGather\Jobs\Handler\JobHandler;
use FinGather\Service\Queue\Enum\QueueEnum;
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
					QueueEnum::EmailVerify->value => EmailVerifyHandler::class,
					QueueEnum::ApiImportPrepareCheck->value => ApiImportPrepareCheckHandler::class,
					QueueEnum::ApiImportProcessCheck->value => ApiImportProcessCheckHandler::class,
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
