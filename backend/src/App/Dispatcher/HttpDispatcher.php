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
use function Safe\file_put_contents;
use const FILE_APPEND;

final class HttpDispatcher implements Dispatcher
{
	private PSR7Worker $psr7;

	public function canServe(EnvironmentInterface $env): bool
	{
		return $env->getMode() === Mode::MODE_HTTP;
	}

	public function serve(): void
	{
		$worker = Worker::create();
		$factory = new Psr17Factory();
		$this->psr7 = new PSR7Worker($worker, $factory, $factory, $factory);

		$application = ApplicationFactory::create();

		$logger = $application->container->get(LoggerInterface::class);
		assert($logger instanceof LoggerInterface);

		$currentTransactionProvider = $application->container->get(CurrentTransactionProvider::class);
		assert($currentTransactionProvider instanceof CurrentTransactionProvider);

		try {
			while (true) {
				$request = $this->psr7->waitRequest();

				if ($request === null) {
					$logger->debug('Request is null: worker will terminate.');

					return;
				}

				$response = $application->handler->handle($request);
				$this->psr7->respond($response);

				//fix SQL cache for each request
				$application->dbContext->getOrm()->getHeap()->clean();

				$currentTransactionProvider->clear();

				// Clean up hanging references
				gc_collect_cycles();
			}
		} catch (\Throwable $e) {
			$this->handleException($e, $logger, $request ?? null);
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
