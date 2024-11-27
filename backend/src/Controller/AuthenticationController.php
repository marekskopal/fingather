<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\CredentialsDto;
use FinGather\Dto\EmailExistsDto;
use FinGather\Dto\RefreshTokenDto;
use FinGather\Dto\SignUpDto;
use FinGather\Model\Entity\Enum\UserRoleEnum;
use FinGather\Response\BoolResponse;
use FinGather\Response\ConflictResponse;
use FinGather\Response\NotAuthorizedResponse;
use FinGather\Response\NotFoundResponse;
use FinGather\Route\Routes;
use FinGather\Service\Authentication\AuthenticationService;
use FinGather\Service\Authentication\Exceptions\AuthenticationException;
use FinGather\Service\Provider\CurrencyProvider;
use FinGather\Service\Provider\UserProvider;
use FinGather\Service\Request\RequestService;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RoutePost;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AuthenticationController
{
	public function __construct(
		private readonly AuthenticationService $authenticationService,
		private readonly CurrencyProvider $currencyProvider,
		private readonly UserProvider $userProvider,
		private readonly RequestService $requestService,
	) {
	}

	#[RoutePost(Routes::AuthenticationLogin->value)]
	public function actionPostLogin(ServerRequestInterface $request): ResponseInterface
	{
		$credentials = $this->requestService->getRequestBodyDto($request, CredentialsDto::class);

		try {
			return new JsonResponse($this->authenticationService->authenticate($credentials));
		} catch (AuthenticationException) {
			return new JsonResponse('Email or password id invalid.', 401);
		}
	}

	#[RoutePost(Routes::AuthenticationRefreshToken->value)]
	public function actionPostRefreshToken(ServerRequestInterface $request): ResponseInterface
	{
		$refreshToken = $this->requestService->getRequestBodyDto($request, RefreshTokenDto::class);

		try {
			JWT::decode(
				$refreshToken->refreshToken,
				new Key((string) getenv('AUTHORIZATION_TOKEN_KEY'), AuthenticationService::TokenAlgorithm),
			);
		} catch (ExpiredException) {
			return new NotAuthorizedResponse('RefreshToken is expired.');
		} catch (\Throwable) {
			return new NotAuthorizedResponse('Invalid RefreshToken.');
		}

		$user = $this->requestService->getUser($request);

		return new JsonResponse($this->authenticationService->createAuthentication($user));
	}

	#[RoutePost(Routes::AuthenticationSignUp->value)]
	public function actionPostSignUp(ServerRequestInterface $request): ResponseInterface
	{
		$signUp = $this->requestService->getRequestBodyDto($request, SignUpDto::class);

		$existsUser = $this->userProvider->getUserByEmail($signUp->email);
		if ($existsUser !== null) {
			return new ConflictResponse('User with email "' . $signUp->email . '" already exists.');
		}

		$defaultCurrency = $this->currencyProvider->getCurrency($signUp->defaultCurrencyId);
		if ($defaultCurrency === null) {
			return new NotFoundResponse('Currency with id "' . $signUp->defaultCurrencyId . '" was not found.');
		}

		$this->userProvider->createUser(
			email: $signUp->email,
			password: $signUp->password,
			name: $signUp->name,
			defaultCurrency: $defaultCurrency,
			role: UserRoleEnum::User,
			isEmailVerified: false,
		);

		return new JsonResponse($this->authenticationService->authenticate(new CredentialsDto(
			$signUp->email,
			$signUp->password,
		)));
	}

	#[RoutePost(Routes::AuthenticationEmailExists->value)]
	public function actionPostEmailExists(ServerRequestInterface $request): ResponseInterface
	{
		$emailExistsDto = $this->requestService->getRequestBodyDto($request, EmailExistsDto::class);

		$existsUser = $this->userProvider->getUserByEmail($emailExistsDto->email);

		return new BoolResponse($existsUser !== null);
	}
}
