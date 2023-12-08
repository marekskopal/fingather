<?php

declare(strict_types=1);

namespace FinGather\App;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Worker;
use Throwable;
use function Safe\file_put_contents;
use function Safe\json_encode;
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

				//try {
					$response = $handler->handle($request);
					$this->psr7->respond($response);
				//} catch (UnauthorizedException $e) {
				//	// FIXME: UnauthorizedException is Acl-only code. Push into middleware
				//	$this->psr7->respond(new UnauthorizedResponse());
				//	$logger?->warning($e);
				//}

				// Clean up hanging references
				gc_collect_cycles();
			}
		} catch (Throwable $e) {
			$this->handleException($e, $logger, $request ?? null);
		}
	}

	public function handleException(\Throwable $e, ?LoggerInterface $logger, ?ServerRequestInterface $request): void
	{
		$code = $e->getCode() >= 100 && $e->getCode() <= 999 ? $e->getCode() : 500;
		$body = json_encode(['status_code' => $code, 'error' => $e->getMessage() ?: 'Internal Server Error']);

		if ($logger === null) {
			// Failsafe in case the logger is not initialized yet
			file_put_contents('php://stderr', __METHOD__ . ': ' . (string) $e, FILE_APPEND);
		} else {
			/** @see Application::initLogger() */
			// Warning: do not add context: here, it will totally flood the log
			$logger->error($e);
		}

		if ($request === null) {
			// If Application::__construct() throws an exception, then $this->psr7->waitRequest() is never called
			// Dummy wait for a request (roadrunner cannot respond without a request)
			$this->psr7->waitRequest();
		}

		$this->psr7->respond(new Response((int) $code, ['content-type' => 'application/json'], $body));

		// Stop and pass the request to the next worker process
		$this->psr7->getWorker()->stop();
	}
}
