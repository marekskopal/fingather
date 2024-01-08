<?php

declare(strict_types=1);

namespace FinGather\Controller\Admin;

use FinGather\Dto\UserCreateDto;
use FinGather\Dto\UserDto;
use FinGather\Model\Entity\Enum\UserRoleEnum;
use FinGather\Model\Entity\User;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Service\Provider\CurrencyProvider;
use FinGather\Service\Provider\UserProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function Safe\json_decode;

class UserController extends AdminController
{
	public function __construct(
		RequestService $requestService,
		private readonly UserProvider $userProvider,
		private readonly CurrencyProvider $currencyProvider,
	)
	{
		parent::__construct($requestService);
	}

	public function actionGetUsers(ServerRequestInterface $request): ResponseInterface
	{
		$this->checkAdminRole($request);

		$brokers = array_map(
			fn (User $user): UserDto => UserDto::fromEntity($user),
			iterator_to_array($this->userProvider->getUsers()),
		);

		return new JsonResponse($brokers);
	}

	/** @param array{userId: string} $args */
	public function actionGetUser(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$this->checkAdminRole($request);

		$userId = (int) $args['userId'];
		if ($userId < 1) {
			return new NotFoundResponse('User id is required.');
		}

		$user = $this->userProvider->getUser($userId);
		if ($user === null) {
			return new NotFoundResponse('User with id "' . $userId . '" was not found.');
		}

		return new JsonResponse(UserDto::fromEntity($user));
	}

	public function actionCreateUser(ServerRequestInterface $request): ResponseInterface
	{
		$this->checkAdminRole($request);

		/** @var array{email: string, name: string, password: string, defaultCurrencyId: int, role: value-of<UserRoleEnum>} $requestBody */
		$requestBody = json_decode($request->getBody()->getContents(), assoc: true);

		$userCreateDto = UserCreateDto::fromArray($requestBody);

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

	/** @param array{userId: string} $args */
	public function actionUpdateUser(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$this->checkAdminRole($request);

		$userId = (int) $args['userId'];
		if ($userId < 1) {
			return new NotFoundResponse('User id is required.');
		}

		$user = $this->userProvider->getUser($userId);
		if ($user === null) {
			return new NotFoundResponse('User with id "' . $userId . '" was not found.');
		}

		/** @var array{name: string, password: string, defaultCurrencyId: int, role: value-of<UserRoleEnum>} $requestBody */
		$requestBody = json_decode($request->getBody()->getContents(), assoc: true);

		$defaultCurrency = $this->currencyProvider->getCurrency($requestBody['defaultCurrencyId']);
		if ($defaultCurrency === null) {
			return new NotFoundResponse('Currency with id "' . $requestBody['defaultCurrencyId'] . '" was not found.');
		}

		return new JsonResponse(UserDto::fromEntity($this->userProvider->updateUser(
			user: $user,
			password: $requestBody['password'],
			name: $requestBody['name'],
			defaultCurrency: $defaultCurrency,
			role: UserRoleEnum::from($requestBody['role']),
		)));
	}

	/** @param array{userId: string} $args */
	public function actionDeleteUser(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$this->checkAdminRole($request);

		$userId = (int) $args['userId'];
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
