<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\UserDto;
use FinGather\Route\Routes;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteGet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CurrentUserController
{
	public function __construct(private readonly RequestService $requestService)
	{
	}

	#[RouteGet(Routes::CurrentUser->value)]
	public function actionGetCurrentUser(ServerRequestInterface $request): ResponseInterface
	{
		return new JsonResponse(UserDto::fromEntity($this->requestService->getUser($request)));
	}
}
