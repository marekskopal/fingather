<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\CurrentUserUpdateDto;
use FinGather\Dto\UserDto;
use FinGather\Response\ConflictResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\UserProviderInterface;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteGet;
use MarekSkopal\Router\Attribute\RoutePut;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class CurrentUserController
{
	public function __construct(private RequestService $requestService, private UserProviderInterface $userProvider)
	{
	}

	#[RouteGet(Routes::CurrentUser->value)]
	public function actionGetCurrentUser(ServerRequestInterface $request): ResponseInterface
	{
		return new JsonResponse(UserDto::fromEntity($this->requestService->getUser($request)));
	}

	#[RoutePut(Routes::CurrentUser->value)]
	public function actionPutCurrentUser(ServerRequestInterface $request): ResponseInterface
	{
		$user = $this->requestService->getUser($request);
		$currentUserUpdateDto = $this->requestService->getRequestBodyDto($request, CurrentUserUpdateDto::class);

		if ($currentUserUpdateDto->email !== $user->email) {
			$existsUser = $this->userProvider->getUserByEmail($currentUserUpdateDto->email);
			if ($existsUser !== null) {
				return new ConflictResponse('User with email "' . $currentUserUpdateDto->email . '" already exists.');
			}
		}

		$user = $this->userProvider->updateUser(
			user: $user,
			email: $currentUserUpdateDto->email,
			password: $currentUserUpdateDto->password,
			name: $currentUserUpdateDto->name,
			role: $user->role,
		);

		$user = $this->userProvider->updateEmailNotifications($user, $currentUserUpdateDto->isEmailNotificationsEnabled);

		return new JsonResponse(UserDto::fromEntity($user));
	}
}
