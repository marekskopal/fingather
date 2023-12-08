<?php

declare(strict_types=1);

namespace FinGather\Route\Strategy;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use function Safe\json_encode;

class JsonStrategy extends \League\Route\Strategy\JsonStrategy
{
	public function __construct(private readonly LoggerInterface $logger, ResponseFactoryInterface $responseFactory, int $jsonFlags = 0)
	{
		parent::__construct($responseFactory, $jsonFlags);
	}

	public function getThrowableHandler(): MiddlewareInterface
	{
		return new class ($this->responseFactory->createResponse(), $this->logger) implements MiddlewareInterface
		{
			public function __construct(private readonly ResponseInterface $response, private readonly LoggerInterface $logger,)
			{
			}

			public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
			{
				try {
					return $handler->handle($request);
				} catch (\Throwable $exception) {
					$response = $this->response;

					if ($exception instanceof \League\Route\Http\Exception) {
						return $exception->buildJsonResponse($response);
					}

					$this->logger->error($exception);

					$response->getBody()->write(json_encode([
						'status_code' => 500,
						'reason_phrase' => $exception->getMessage(),
					]));

					$response = $response->withAddedHeader('content-type', 'application/json');
					return $response->withStatus(500, (string) strtok($exception->getMessage(), "\n"));
				}
			}
		};
	}
}
