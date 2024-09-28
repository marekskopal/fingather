<?php

declare(strict_types=1);

namespace FinGather\Controller\Admin;

use FinGather\Dto\UserCreateDto;
use FinGather\Dto\UserDto;
use FinGather\Dto\UserWithStatisticDto;
use FinGather\Model\Entity\Enum\UserRoleEnum;
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
use function Safe\json_decode;

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

		$brokers = array_map(
			function (User $user): UserWithStatisticDto {
				return UserWithStatisticDto::fromEntity(
					entity: $user,
					assetCount: $this->assetProvider->countAssets($user),
					transactionCount: $this->transactionProvider->countTransactions($user),
				);
			},
			$this->userProvider->getUsers(),
		);

		return new JsonResponse($brokers);
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

		/** @var array{email: string, name: string, password: string, defaultCurrencyId: int, role: value-of<UserRoleEnum>} $requestBody */
		$requestBody = json_decode($request->getBody()->getContents(), assoc: true);

		$userCreateDto = UserCreateDto::fromArray($requestBody);

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

		/** @var array{name: string, password: string, defaultCurrencyId: int, role: value-of<UserRoleEnum>} $requestBody */
		$requestBody = json_decode($request->getBody()->getContents(), assoc: true);

		return new JsonResponse(UserDto::fromEntity($this->userProvider->updateUser(
			user: $user,
			password: $requestBody['password'],
			name: $requestBody['name'],
			role: UserRoleEnum::from($requestBody['role']),
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
