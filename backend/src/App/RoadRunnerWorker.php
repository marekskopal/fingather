<?php

declare(strict_types=1);

namespace FinGather\App;

use FinGather\Jobs\Handler\EmailVerifyHandler;
use FinGather\Jobs\Handler\JobHandler;
use FinGather\Middleware\Exception\NotAuthorizedException;
use FinGather\Response\ErrorResponse;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Spiral\RoadRunner\Environment;
use Spiral\RoadRunner\Environment\Mode;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Jobs\Consumer;
use Spiral\RoadRunner\Worker;
use Throwable;
use function Safe\file_put_contents;
use const FILE_APPEND;

final class RoadRunnerWorker
{
	private PSR7Worker $psr7;

	private readonly string $mode;

	public function __construct()
	{
		$env = Environment::fromGlobals();
		$this->mode = $env->getMode();
	}

	public function run(): void
	{
		if ($this->mode === Mode::MODE_JOBS) {
			$this->runJobs();
		}

		$this->runHttp();
	}

	private function runHttp(): void
	{
		$worker = Worker::create();
		$factory = new Psr17Factory();
		$this->psr7 = new PSR7Worker($worker, $factory, $factory, $factory);

		$application = ApplicationFactory::create();

		$logger = $application->container->get(LoggerInterface::class);
		assert($logger instanceof LoggerInterface);

		try {
			while (true) {
				$request = $this->psr7->waitRequest();

				if ($request === null) {
					$logger->debug('Request is null: worker will terminate.');

					return;
				}

				$response = $application->handler->handle($request);
				$this->psr7->respond($response);

				// Clean up hanging references
				gc_collect_cycles();
			}
		} catch (Throwable $e) {
			$this->handleException($e, $logger, $request ?? null);
		}
	}

	private function runJobs(): void
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

				$task->complete();

				gc_collect_cycles();
			} catch (\Throwable $e) {
				$task->fail($e);
			}
		}
	}

	private function handleException(\Throwable $e, ?LoggerInterface $logger, ?ServerRequestInterface $request): void
	{
		if ($logger === null) {
			// Failsafe in case the logger is not initialized yet
			file_put_contents('php://stderr', __METHOD__ . ': ' . (string) $e, FILE_APPEND);
		} else {
			switch ($e::class) {
				case NotAuthorizedException::class:
					$logger->warning($e);
					break;
				default:
					$logger->error($e);
			}
		}

		if ($request === null) {
			// Dummy wait for a request (roadrunner cannot respond without a request)
			$this->psr7->waitRequest();
		}

		$this->psr7->respond(ErrorResponse::fromException($e));

		// Stop and pass the request to the next worker process
		$this->psr7->getWorker()->stop();
	}
}
