<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Route\Routes;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\ORM\Database\DatabaseInterface;
use MarekSkopal\Router\Attribute\RouteGet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[RouteGet(Routes::Health->value)]
final readonly class HealthController
{
	public function __construct(private DatabaseInterface $database)
	{
	}

	public function __invoke(ServerRequestInterface $request): ResponseInterface
	{
		$this->database->getPdo()->query('SELECT 1');

		return new JsonResponse(['status' => 200, 'message' => 'OK']);
	}
}
