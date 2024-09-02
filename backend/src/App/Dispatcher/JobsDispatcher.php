<?php

declare(strict_types=1);

namespace FinGather\App\Dispatcher;

use FinGather\App\ApplicationFactory;
use FinGather\Jobs\Handler\ApiImportPrepareCheckHandler;
use FinGather\Jobs\Handler\ApiImportProcessCheckHandler;
use FinGather\Jobs\Handler\EmailVerifyHandler;
use FinGather\Jobs\Handler\JobHandler;
use FinGather\Service\Queue\Enum\QueueEnum;
use Psr\Log\LoggerInterface;
use Spiral\RoadRunner\Environment\Mode;
use Spiral\RoadRunner\EnvironmentInterface;
use Spiral\RoadRunner\Jobs\Consumer;
use function Safe\file_put_contents;
use const FILE_APPEND;

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

		$logger = $application->container->get(LoggerInterface::class);
		assert($logger instanceof LoggerInterface);

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

				//fix SQL cache for each task
				$application->dbContext->getOrm()->getHeap()->clean();

				// Clean up hanging references
				gc_collect_cycles();
			} catch (\Throwable $e) {
				$this->handleException($e, $logger);

				$task->nack($e);
			}
		}
	}

	private function handleException(\Throwable $e, ?LoggerInterface $logger): void
	{
		if ($logger === null) {
			// Failsafe in case the logger is not initialized yet
			file_put_contents('php://stderr', __METHOD__ . ': ' . (string) $e, FILE_APPEND);
			return;
		}

		$logger->error($e);
	}
}
