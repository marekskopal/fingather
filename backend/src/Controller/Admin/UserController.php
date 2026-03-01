<?php

declare(strict_types=1);

namespace FinGather\Controller\Admin;

use FinGather\Dto\UserCreateDto;
use FinGather\Dto\UserDto;
use FinGather\Dto\UserListDto;
use FinGather\Dto\UserUpdateDto;
use FinGather\Dto\UserWithStatisticDto;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\Enum\OrderDirectionEnum;
use FinGather\Model\Repository\Enum\UserOrderByEnum;
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

final readonly class UserController extends AdminController
{
	public function __construct(
		RequestService $requestService,
		private UserProvider $userProvider,
		private CurrencyProvider $currencyProvider,
		private AssetProvider $assetProvider,
		private TransactionProvider $transactionProvider,
	) {
		parent::__construct($requestService);
	}

	#[RouteGet(Routes::AdminUsers->value)]
	public function actionGetUsers(ServerRequestInterface $request): ResponseInterface
	{
		/** @var array{limit?: string, offset?: string, orderBy?: string, orderDirection?: string} $queryParams */
		$queryParams = $request->getQueryParams();

		$limit = ($queryParams['limit'] ?? null) !== null ? (int) $queryParams['limit'] : null;
		$offset = ($queryParams['offset'] ?? null) !== null ? (int) $queryParams['offset'] : null;

		$orderByColumn = ($queryParams['orderBy'] ?? null) !== null ? UserOrderByEnum::tryFrom($queryParams['orderBy']) : null;
		$orderDirection = ($queryParams['orderDirection'] ?? null) !== null
			? OrderDirectionEnum::tryFrom($queryParams['orderDirection'])
			: null;

		$orderBy = $orderByColumn !== null
			? [$orderByColumn->value => $orderDirection ?? OrderDirectionEnum::DESC]
			: [UserOrderByEnum::Id->value => OrderDirectionEnum::DESC];

		$users = array_map(
			function (User $user): UserWithStatisticDto {
				return UserWithStatisticDto::fromEntity(
					entity: $user,
					assetCount: $this->assetProvider->countAssets($user),
					transactionCount: $this->transactionProvider->countTransactions($user),
				);
			},
			iterator_to_array($this->userProvider->getUsers($limit, $offset, $orderBy), false),
		);

		$count = $this->userProvider->countUsers();

		return new JsonResponse(new UserListDto($users, $count));
	}

	#[RouteGet(Routes::AdminUser->value)]
	public function actionGetUser(ServerRequestInterface $request, int $userId): ResponseInterface
	{
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
