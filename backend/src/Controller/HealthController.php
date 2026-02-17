<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Route\Routes;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteGet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[RouteGet(Routes::Health->value)]
final class HealthController
{
	public function __invoke(ServerRequestInterface $request): ResponseInterface
	{
		return new JsonResponse(['status' => 200, 'message' => 'OK']);
	}
}
