<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\CredentialsDto;
use FinGather\Dto\EmailExistsDto;
use FinGather\Dto\GoogleClientIdDto;
use FinGather\Dto\GoogleLoginDto;
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
use FinGather\Service\Authentication\Exceptions\GoogleAuthException;
use FinGather\Service\Authentication\GoogleAuthService;
use FinGather\Service\Provider\CurrencyProvider;
use FinGather\Service\Provider\UserProvider;
use FinGather\Service\Request\RequestService;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteGet;
use MarekSkopal\Router\Attribute\RoutePost;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final readonly class AuthenticationController
{
	public function __construct(
		private AuthenticationService $authenticationService,
		private GoogleAuthService $googleAuthService,
		private CurrencyProvider $currencyProvider,
		private UserProvider $userProvider,
		private RequestService $requestService,
		private LoggerInterface $logger,
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
			$decodedRefreshToken = JWT::decode(
				$refreshToken->refreshToken,
				new Key((string) getenv('AUTHORIZATION_TOKEN_KEY'), AuthenticationService::TokenAlgorithm),
			);
		} catch (ExpiredException) {
			return new NotAuthorizedResponse('RefreshToken is expired.');
		} catch (\Throwable) {
			return new NotAuthorizedResponse('Invalid RefreshToken.');
		}

		$user = $this->requestService->getUser($request);

		if ($decodedRefreshToken->id !== $user->id) {
			return new NotAuthorizedResponse('Invalid RefreshToken.');
		}

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

	#[RouteGet(Routes::AuthenticationGoogleClientId->value)]
	public function actionGetApiKey(ServerRequestInterface $request): ResponseInterface
	{
		return new JsonResponse(new GoogleClientIdDto((string) getenv('GOOGLE_CLIENT_ID')));
	}

	#[RoutePost(Routes::AuthenticationGoogleLogin->value)]
	public function actionPostGoogleLogin(ServerRequestInterface $request): ResponseInterface
	{
		$googleLoginDto = $this->requestService->getRequestBodyDto($request, GoogleLoginDto::class);

		try {
			$tokenInfo = $this->googleAuthService->verifyIdToken($googleLoginDto->idToken);
		} catch (GoogleAuthException $e) {
			$this->logger->info($e);

			return new NotAuthorizedResponse('Invalid Google token.');
		}

		// Check if user exists by Google ID
		$user = $this->userProvider->getUserByGoogleId($tokenInfo->sub);
		if ($user !== null) {
			$this->userProvider->updateLastLoggedIn($user);

			return new JsonResponse($this->authenticationService->createAuthentication($user));
		}

		// Check if user exists by email
		$user = $this->userProvider->getUserByEmail($tokenInfo->email);
		if ($user !== null) {
			// Link Google account to existing user
			$this->userProvider->linkGoogleAccount($user, $tokenInfo->sub);
		} else {
			// New user - need currency selection
			if ($googleLoginDto->defaultCurrencyId === null) {
				return new JsonResponse([
					'requiresCurrency' => true,
					'email' => $tokenInfo->email,
					'name' => $tokenInfo->name,
				]);
			}

			// Create new user
			$defaultCurrency = $this->currencyProvider->getCurrency($googleLoginDto->defaultCurrencyId);
			if ($defaultCurrency === null) {
				return new NotFoundResponse('Currency with id "' . $googleLoginDto->defaultCurrencyId . '" was not found.');
			}

			$user = $this->userProvider->createUserFromGoogle(
				email: $tokenInfo->email,
				name: $tokenInfo->name,
				googleId: $tokenInfo->sub,
				defaultCurrency: $defaultCurrency,
			);
		}

		$this->userProvider->updateLastLoggedIn($user);

		return new JsonResponse($this->authenticationService->createAuthentication($user));
	}
}
