<?php

declare(strict_types=1);

namespace FinGather\Controller;

use DateTimeImmutable;
use FinGather\Dto\CredentialsDto;
use FinGather\Dto\EmailExistsDto;
use FinGather\Dto\GoogleClientIdDto;
use FinGather\Dto\GoogleLoginDto;
use FinGather\Dto\PasswordResetConfirmDto;
use FinGather\Dto\PasswordResetRequestDto;
use FinGather\Dto\RefreshTokenDto;
use FinGather\Dto\SignUpDto;
use FinGather\Model\Entity\Enum\UserRoleEnum;
use FinGather\Response\BoolResponse;
use FinGather\Response\ConflictResponse;
use FinGather\Response\ErrorResponse;
use FinGather\Response\NotAuthorizedResponse;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Route\Routes;
use FinGather\Service\Authentication\AuthenticationServiceInterface;
use FinGather\Service\Authentication\Exceptions\AuthenticationException;
use FinGather\Service\Authentication\Exceptions\GoogleAuthException;
use FinGather\Service\Authentication\GoogleAuthServiceInterface;
use FinGather\Service\Provider\CurrencyProviderInterface;
use FinGather\Service\Provider\PasswordResetProviderInterface;
use FinGather\Service\Provider\UserProviderInterface;
use FinGather\Service\Request\RequestServiceInterface;
use FinGather\Validator\PasswordValidator;
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
	private const string RefreshTokenCookieName = 'refresh_token';
	private const int RefreshTokenCookieMaxAge = 604800;

	public function __construct(
		private AuthenticationServiceInterface $authenticationService,
		private GoogleAuthServiceInterface $googleAuthService,
		private CurrencyProviderInterface $currencyProvider,
		private UserProviderInterface $userProvider,
		private PasswordResetProviderInterface $passwordResetProvider,
		private RequestServiceInterface $requestService,
		private LoggerInterface $logger,
	) {
	}

	#[RoutePost(Routes::AuthenticationLogin->value)]
	public function actionPostLogin(ServerRequestInterface $request): ResponseInterface
	{
		$credentials = $this->requestService->getRequestBodyDto($request, CredentialsDto::class);

		try {
			$authDto = $this->authenticationService->authenticate($credentials);
			return $this->withRefreshTokenCookie(new JsonResponse($authDto), $authDto->refreshToken);
		} catch (AuthenticationException) {
			return new JsonResponse('Email or password id invalid.', 401);
		}
	}

	#[RoutePost(Routes::AuthenticationLogout->value)]
	public function actionPostLogout(ServerRequestInterface $request): ResponseInterface
	{
		return $this->withClearedRefreshTokenCookie(new OkResponse());
	}

	#[RoutePost(Routes::AuthenticationRefreshToken->value)]
	public function actionPostRefreshToken(ServerRequestInterface $request): ResponseInterface
	{
		$cookieValue = $request->getCookieParams()[self::RefreshTokenCookieName] ?? null;
		$refreshTokenValue = is_string($cookieValue) ? $cookieValue : null;

		if ($refreshTokenValue === null) {
			$refreshTokenDto = $this->requestService->getRequestBodyDto($request, RefreshTokenDto::class);
			$refreshTokenValue = $refreshTokenDto->refreshToken;
		}

		if ($refreshTokenValue === null) {
			return new NotAuthorizedResponse('RefreshToken not found.');
		}

		$tokenKey = getenv('AUTHORIZATION_TOKEN_KEY');
		if ($tokenKey === false || $tokenKey === '') {
			throw new \RuntimeException('AUTHORIZATION_TOKEN_KEY environment variable is not configured.');
		}

		try {
			$decodedRefreshToken = JWT::decode(
				$refreshTokenValue,
				new Key($tokenKey, AuthenticationServiceInterface::TokenAlgorithm),
			);
		} catch (ExpiredException) {
			return new NotAuthorizedResponse('RefreshToken is expired.');
		} catch (\UnexpectedValueException | \InvalidArgumentException | \DomainException) {
			return new NotAuthorizedResponse('Invalid RefreshToken.');
		}

		$user = $this->requestService->getUser($request);

		if ($decodedRefreshToken->id !== $user->id) {
			return new NotAuthorizedResponse('Invalid RefreshToken.');
		}

		$authDto = $this->authenticationService->createAuthentication($user);
		return $this->withRefreshTokenCookie(new JsonResponse($authDto), $authDto->refreshToken);
	}

	#[RoutePost(Routes::AuthenticationSignUp->value)]
	public function actionPostSignUp(ServerRequestInterface $request): ResponseInterface
	{
		$signUp = $this->requestService->getRequestBodyDto($request, SignUpDto::class);

		if (!PasswordValidator::isValid($signUp->password)) {
			return new ErrorResponse('Password does not meet requirements.', 422);
		}

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
			locale: $signUp->locale,
		);

		$authDto = $this->authenticationService->authenticate(new CredentialsDto($signUp->email, $signUp->password));
		return $this->withRefreshTokenCookie(new JsonResponse($authDto), $authDto->refreshToken);
	}

	#[RoutePost(Routes::PasswordResetRequest->value)]
	public function actionPostPasswordResetRequest(ServerRequestInterface $request): ResponseInterface
	{
		$dto = $this->requestService->getRequestBodyDto($request, PasswordResetRequestDto::class);

		$user = $this->userProvider->getUserByEmail($dto->email);
		if ($user?->password !== null) {
			$this->passwordResetProvider->createPasswordReset($user);
		}

		// Always return OK to avoid leaking whether the email exists
		return new OkResponse();
	}

	#[RoutePost(Routes::PasswordResetConfirm->value)]
	public function actionPostPasswordResetConfirm(ServerRequestInterface $request): ResponseInterface
	{
		$dto = $this->requestService->getRequestBodyDto($request, PasswordResetConfirmDto::class);

		$passwordReset = $this->passwordResetProvider->getPasswordReset($dto->token);
		if ($passwordReset === null) {
			return new NotFoundResponse('Password reset token not found or expired.');
		}

		$expiry = $passwordReset->createdAt->modify('+24 hours');
		if ($expiry < new DateTimeImmutable()) {
			$this->passwordResetProvider->deletePasswordReset($passwordReset);
			return new NotFoundResponse('Password reset token not found or expired.');
		}

		if (!PasswordValidator::isValid($dto->password)) {
			return new ErrorResponse('Password does not meet requirements.', 422);
		}

		$this->userProvider->resetPassword($passwordReset->user, $dto->password);
		$this->passwordResetProvider->deletePasswordReset($passwordReset);

		return new OkResponse();
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

			$authDto = $this->authenticationService->createAuthentication($user);
			return $this->withRefreshTokenCookie(new JsonResponse($authDto), $authDto->refreshToken);
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
				locale: $googleLoginDto->locale,
			);
		}

		$this->userProvider->updateLastLoggedIn($user);

		$authDto = $this->authenticationService->createAuthentication($user);
		return $this->withRefreshTokenCookie(new JsonResponse($authDto), $authDto->refreshToken);
	}

	private function withRefreshTokenCookie(ResponseInterface $response, string $refreshToken): ResponseInterface
	{
		return $response->withAddedHeader(
			'Set-Cookie',
			sprintf(
				'%s=%s; Max-Age=%d; Path=%s; HttpOnly; Secure; SameSite=Strict',
				self::RefreshTokenCookieName,
				$refreshToken,
				self::RefreshTokenCookieMaxAge,
				Routes::AuthenticationRefreshToken->value,
			),
		);
	}

	private function withClearedRefreshTokenCookie(ResponseInterface $response): ResponseInterface
	{
		return $response->withAddedHeader(
			'Set-Cookie',
			sprintf(
				'%s=; Max-Age=0; Path=%s; HttpOnly; Secure; SameSite=Strict',
				self::RefreshTokenCookieName,
				Routes::AuthenticationRefreshToken->value,
			),
		);
	}
}
