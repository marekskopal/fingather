<?php

declare(strict_types=1);

namespace FinGather\Route\Strategy;

use FinGather\Middleware\Exception\NotAuthorizedException;
use FinGather\Response\ErrorResponse;
use League\Route\Route;
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

	public function invokeRouteCallable(Route $route, ServerRequestInterface $request): ResponseInterface
	{
		$controller = $route->getCallable($this->getContainer());

		$vars = array_map(fn (string $item): int|string => filter_var($item, FILTER_VALIDATE_INT) !== false ? (int) $item : $item, $route->getVars());

		$response = $controller($request, ...$vars);

		if ($this->isJsonSerializable($response)) {
			$body = json_encode($response, $this->jsonFlags);
			$response = $this->responseFactory->createResponse();
			$response->getBody()->write($body);
		}

		return $this->decorateResponse($response);
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
