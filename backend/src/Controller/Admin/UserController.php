<?php

declare(strict_types=1);

namespace FinGather\Controller\Admin;

use FinGather\Dto\UserCreateDto;
use FinGather\Dto\UserDto;
use FinGather\Dto\UserUpdateDto;
use FinGather\Dto\UserWithStatisticDto;
use FinGather\Model\Entity\User;
use FinGather\Response\ConflictResponse;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\AssetProvider;
use FinGather\Service\Provider\CurrencyProvider;
use FinGather\Service\Provider\TransactionProvider;
use FinGather\Service\Provider\UserProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteDelete;
use MarekSkopal\Router\Attribute\RouteGet;
use MarekSkopal\Router\Attribute\RoutePost;
use MarekSkopal\Router\Attribute\RoutePut;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class UserController extends AdminController
{
	public function __construct(
		RequestService $requestService,
		private readonly UserProvider $userProvider,
		private readonly CurrencyProvider $currencyProvider,
		private readonly AssetProvider $assetProvider,
		private readonly TransactionProvider $transactionProvider,
	) {
		parent::__construct($requestService);
	}

	#[RouteGet(Routes::AdminUsers->value)]
	public function actionGetUsers(ServerRequestInterface $request): ResponseInterface
	{
		$this->checkAdminRole($request);

		$users = array_map(
			function (User $user): UserWithStatisticDto {
				return UserWithStatisticDto::fromEntity(
					entity: $user,
					assetCount: $this->assetProvider->countAssets($user),
					transactionCount: $this->transactionProvider->countTransactions($user),
				);
			},
			iterator_to_array($this->userProvider->getUsers(), false),
		);

		return new JsonResponse($users);
	}

	#[RouteGet(Routes::AdminUser->value)]
	public function actionGetUser(ServerRequestInterface $request, int $userId): ResponseInterface
	{
		$this->checkAdminRole($request);

		if ($userId < 1) {
			return new NotFoundResponse('User id is required.');
		}

		$user = $this->userProvider->getUser($userId);
		if ($user === null) {
			return new NotFoundResponse('User with id "' . $userId . '" was not found.');
		}

		return new JsonResponse(UserDto::fromEntity($user));
	}

	#[RoutePost(Routes::AdminUsers->value)]
	public function actionCreateUser(ServerRequestInterface $request): ResponseInterface
	{
		$this->checkAdminRole($request);

		$userCreateDto = $this->requestService->getRequestBodyDto($request, UserCreateDto::class);

		$existsUser = $this->userProvider->getUserByEmail($userCreateDto->email);
		if ($existsUser !== null) {
			return new ConflictResponse('User with email "' . $userCreateDto->email . '" already exists.');
		}

		$defaultCurrency = $this->currencyProvider->getCurrency($userCreateDto->defaultCurrencyId);
		if ($defaultCurrency === null) {
			return new NotFoundResponse('Currency with id "' . $userCreateDto->defaultCurrencyId . '" was not found.');
		}

		return new JsonResponse(UserDto::fromEntity($this->userProvider->createUser(
			email: $userCreateDto->email,
			password: $userCreateDto->password,
			name: $userCreateDto->name,
			defaultCurrency: $defaultCurrency,
			role: $userCreateDto->role,
			isEmailVerified: true,
		)));
	}

	#[RoutePut(Routes::AdminUser->value)]
	public function actionUpdateUser(ServerRequestInterface $request, int $userId): ResponseInterface
	{
		$this->checkAdminRole($request);

		if ($userId < 1) {
			return new NotFoundResponse('User id is required.');
		}

		$user = $this->userProvider->getUser($userId);
		if ($user === null) {
			return new NotFoundResponse('User with id "' . $userId . '" was not found.');
		}

		$userUpdateDto = $this->requestService->getRequestBodyDto($request, UserUpdateDto::class);

		return new JsonResponse(UserDto::fromEntity($this->userProvider->updateUser(
			user: $user,
			email: $user->email,
			password: $userUpdateDto->password,
			name: $userUpdateDto->name,
			role: $userUpdateDto->role,
		)));
	}

	#[RouteDelete(Routes::AdminUser->value)]
	public function actionDeleteUser(ServerRequestInterface $request, int $userId): ResponseInterface
	{
		$this->checkAdminRole($request);

		if ($userId < 1) {
			return new NotFoundResponse('User id is required.');
		}

		$user = $this->userProvider->getUser($userId);
		if ($user === null) {
			return new NotFoundResponse('User with id "' . $userId . '" was not found.');
		}

		$this->userProvider->deleteUser($user);

		return new OkResponse();
	}
}
