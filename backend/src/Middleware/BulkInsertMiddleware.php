<?php

declare(strict_types=1);

namespace FinGather\Middleware;

use FinGather\Service\Provider\BulkQueryProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class BulkInsertMiddleware implements MiddlewareInterface
{
	public function __construct(private readonly BulkQueryProvider $bulkInsertProvider)
	{
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$response = $handler->handle($request);

		$this->bulkInsertProvider->runAll();

		return $response;
	}
}
