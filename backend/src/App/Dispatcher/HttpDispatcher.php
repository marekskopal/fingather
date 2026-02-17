<?php

declare(strict_types=1);

namespace FinGather\App\Dispatcher;

use FinGather\App\ApplicationFactory;
use FinGather\Middleware\Exception\NotAuthorizedException;
use FinGather\Response\ErrorResponse;
use FinGather\Service\Provider\CurrentTransactionProvider;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Spiral\RoadRunner\Environment\Mode;
use Spiral\RoadRunner\EnvironmentInterface;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Worker;
use const FILE_APPEND;

final class HttpDispatcher implements Dispatcher
{
	public function canServe(EnvironmentInterface $env): bool
	{
		return $env->getMode() === Mode::MODE_HTTP;
	}

	public function serve(): void
	{
		$worker = Worker::create();
		$factory = new Psr17Factory();
		$psr7Worker = new PSR7Worker($worker, $factory, $factory, $factory);

		$application = ApplicationFactory::create();

		$logger = $application->container->get(LoggerInterface::class);
		assert($logger instanceof LoggerInterface);

		$currentTransactionProvider = $application->container->get(CurrentTransactionProvider::class);
		assert($currentTransactionProvider instanceof CurrentTransactionProvider);

		try {
			while (true) {
				$request = $psr7Worker->waitRequest();

				if ($request === null) {
					$logger->debug('Request is null: worker will terminate.');

					return;
				}

				$response = $application->handler->handle($request);
				$psr7Worker->respond($response);

				//clear SQL cache for each request
				$application->dbContext->getOrm()->getEntityCache()->clear();

				$currentTransactionProvider->clear();

				// Clean up hanging references
				gc_collect_cycles();
			}
		} catch (\Throwable $e) {
			$this->handleException($e, $psr7Worker, $logger, $request ?? null);
		}
	}

	private function handleException(
		\Throwable $e,
		PSR7Worker $psr7Worker,
		?LoggerInterface $logger,
		?ServerRequestInterface $request,
	): void
	{
		if ($logger === null) {
			// Failsafe in case the logger is not initialized yet
			file_put_contents('php://stderr', __METHOD__ . ': ' . (string) $e, FILE_APPEND);
		} else {
			switch ($e::class) {
				case NotAuthorizedException::class:
					$logger->warning($e);
					break;
				case \InvalidArgumentException::class:
					str_starts_with($e->getMessage(), 'Unable to parse URI:')
						? $logger->info($e)
						: $logger->error($e);
					break;

				default:
					$logger->error($e);
			}
		}

		if ($request === null) {
			// Dummy wait for a request (roadrunner cannot respond without a request)
			$psr7Worker->waitRequest();
		}

		$psr7Worker->respond(ErrorResponse::fromException($e));

		// Stop and pass the request to the next worker process
		$psr7Worker->getWorker()->stop();
	}
}
