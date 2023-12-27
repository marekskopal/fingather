<?php

declare(strict_types=1);

namespace FinGather\App;

use FinGather\Middleware\Exception\NotAuthorizedException;
use FinGather\Response\ErrorResponse;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Worker;
use Throwable;
use function Safe\file_put_contents;
use const FILE_APPEND;

final class RoadRunnerProcessor
{
	private readonly PSR7Worker $psr7;

	public function __construct()
	{
		// Create new RoadRunner worker from global environment
		$worker = Worker::create();
		$factory = new Psr17Factory();
		$this->psr7 = new PSR7Worker($worker, $factory, $factory, $factory);
	}

	public function __invoke(RequestHandlerInterface $handler, ?LoggerInterface $logger): void
	{
		try {
			while (true) {
				$request = $this->psr7->waitRequest();

				if ($request === null) {
					$logger?->debug('Request is null: worker will terminate.');

					return;
				}

				$response = $handler->handle($request);
				$this->psr7->respond($response);

				// Clean up hanging references
				gc_collect_cycles();
			}
		} catch (Throwable $e) {
			$this->handleException($e, $logger, $request ?? null);
		}
	}

	public function handleException(\Throwable $e, ?LoggerInterface $logger, ?ServerRequestInterface $request): void
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
			// If Application::__construct() throws an exception, then $this->psr7->waitRequest() is never called
			// Dummy wait for a request (roadrunner cannot respond without a request)
			$this->psr7->waitRequest();
		}

		$this->psr7->respond(ErrorResponse::fromException($e));

		// Stop and pass the request to the next worker process
		$this->psr7->getWorker()->stop();
	}
}
