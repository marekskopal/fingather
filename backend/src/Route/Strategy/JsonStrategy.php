<?php

declare(strict_types=1);

namespace FinGather\Route\Strategy;

use FinGather\Middleware\Exception\NotAuthorizedException;
use FinGather\Response\ErrorResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class JsonStrategy extends \League\Route\Strategy\JsonStrategy
{
	public function __construct(private readonly LoggerInterface $logger, ResponseFactoryInterface $responseFactory, int $jsonFlags = 0)
	{
		parent::__construct($responseFactory, $jsonFlags);
	}

	public function getThrowableHandler(): MiddlewareInterface
	{
		return new class ($this->logger) implements MiddlewareInterface
		{
			public function __construct(private readonly LoggerInterface $logger,)
			{
			}

			public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
			{
				try {
					return $handler->handle($request);
				} catch (\Throwable $exception) {
					switch ($exception::class) {
						case NotAuthorizedException::class:
							$this->logger->warning($exception);
							break;
						default:
							$this->logger->error($exception);
					}

					return ErrorResponse::fromException($exception);
				}
			}
		};
	}
}
