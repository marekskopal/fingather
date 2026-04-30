<?php

declare(strict_types=1);

namespace FinGather\Tests\Middleware;

use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CapturingHandler implements RequestHandlerInterface
{
	public ?ServerRequestInterface $capturedRequest = null;

	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		$this->capturedRequest = $request;
		return new Response();
	}
}
