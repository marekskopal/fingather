<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\UserDto;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CurrentUserController
{
	public function __construct(private readonly RequestService $requestService)
	{
	}

	public function actionGetCurrentUser(ServerRequestInterface $request): ResponseInterface
	{
		return new JsonResponse(UserDto::fromEntity($this->requestService->getUser($request)));
	}
}
