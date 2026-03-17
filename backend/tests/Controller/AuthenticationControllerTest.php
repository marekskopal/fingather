<?php

declare(strict_types=1);

namespace FinGather\Tests\Controller;

use DateTimeImmutable;
use FinGather\Controller\AuthenticationController;
use FinGather\Dto\AuthenticationDto;
use FinGather\Dto\CredentialsDto;
use FinGather\Dto\EmailExistsDto;
use FinGather\Dto\GoogleLoginDto;
use FinGather\Dto\PasswordResetConfirmDto;
use FinGather\Dto\PasswordResetRequestDto;
use FinGather\Dto\RefreshTokenDto;
use FinGather\Dto\SignUpDto;
use FinGather\Model\Entity\Enum\LocaleEnum;
use FinGather\Model\Entity\PasswordReset;
use FinGather\Model\Entity\User;
use FinGather\Response\BoolResponse;
use FinGather\Response\ConflictResponse;
use FinGather\Response\ErrorResponse;
use FinGather\Response\NotAuthorizedResponse;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Service\Authentication\AuthenticationServiceInterface;
use FinGather\Service\Authentication\Dto\TokenInfoDto;
use FinGather\Service\Authentication\Exceptions\AuthenticationException;
use FinGather\Service\Authentication\Exceptions\GoogleAuthException;
use FinGather\Service\Authentication\GoogleAuthServiceInterface;
use FinGather\Service\Provider\CurrencyProvider;
use FinGather\Service\Provider\PasswordResetProvider;
use FinGather\Service\Provider\UserProvider;
use FinGather\Service\Request\RequestServiceInterface;
use FinGather\Tests\Fixtures\Model\Entity\CurrencyFixture;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use FinGather\Validator\PasswordValidator;
use Firebase\JWT\JWT;
use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

#[CoversClass(AuthenticationController::class)]
#[UsesClass(User::class)]
#[UsesClass(PasswordReset::class)]
#[UsesClass(NotAuthorizedResponse::class)]
#[UsesClass(ConflictResponse::class)]
#[UsesClass(ErrorResponse::class)]
#[UsesClass(BoolResponse::class)]
#[UsesClass(OkResponse::class)]
#[UsesClass(NotFoundResponse::class)]
#[UsesClass(AuthenticationException::class)]
#[UsesClass(GoogleAuthException::class)]
#[UsesClass(PasswordValidator::class)]
#[UsesClass(CredentialsDto::class)]
#[UsesClass(RefreshTokenDto::class)]
#[UsesClass(SignUpDto::class)]
#[UsesClass(PasswordResetRequestDto::class)]
#[UsesClass(PasswordResetConfirmDto::class)]
#[UsesClass(EmailExistsDto::class)]
#[UsesClass(GoogleLoginDto::class)]
#[UsesClass(TokenInfoDto::class)]
#[UsesClass(AuthenticationDto::class)]
final class AuthenticationControllerTest extends TestCase
{
	private AuthenticationServiceInterface&Stub $authenticationService;

	private GoogleAuthServiceInterface&Stub $googleAuthService;

	private CurrencyProvider&Stub $currencyProvider;

	private UserProvider&Stub $userProvider;

	private PasswordResetProvider&Stub $passwordResetProvider;

	private RequestServiceInterface&Stub $requestService;

	private LoggerInterface&Stub $logger;

	private AuthenticationController $authenticationController;

	private const string TokenKey = 'test-secret-key-long-enough-for-hs256-algorithm';

	protected function setUp(): void
	{
		$this->authenticationService = $this::createStub(AuthenticationServiceInterface::class);
		$authDto = new AuthenticationDto('test-access-token', 'test-refresh-token', 1);
		$this->authenticationService->method('authenticate')->willReturn($authDto);
		$this->authenticationService->method('createAuthentication')->willReturn($authDto);

		$this->googleAuthService = $this::createStub(GoogleAuthServiceInterface::class);
		$this->currencyProvider = $this::createStub(CurrencyProvider::class);
		$this->userProvider = $this::createStub(UserProvider::class);
		$this->passwordResetProvider = $this::createStub(PasswordResetProvider::class);
		$this->requestService = $this::createStub(RequestServiceInterface::class);
		$this->logger = $this::createStub(LoggerInterface::class);

		$this->authenticationController = new AuthenticationController(
			$this->authenticationService,
			$this->googleAuthService,
			$this->currencyProvider,
			$this->userProvider,
			$this->passwordResetProvider,
			$this->requestService,
			$this->logger,
		);
	}

	protected function tearDown(): void
	{
		putenv('AUTHORIZATION_TOKEN_KEY');
	}

	// --- actionPostLogin ---

	public function testPostLoginReturnsJsonResponse(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn(
			new CredentialsDto('test@example.com', 'Password1!'),
		);

		$response = $this->authenticationController->actionPostLogin(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	public function testPostLoginAuthExceptionReturnsJsonResponse401(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn(
			new CredentialsDto('test@example.com', 'wrong'),
		);
		$this->authenticationService->method('authenticate')->willThrowException(new AuthenticationException('Invalid'));

		$response = $this->authenticationController->actionPostLogin(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(JsonResponse::class, $response);
		self::assertSame(401, $response->getStatusCode());
	}

	// --- actionPostRefreshToken ---

	public function testPostRefreshTokenValidReturnsJsonResponse(): void
	{
		putenv('AUTHORIZATION_TOKEN_KEY=' . self::TokenKey);

		$token = JWT::encode(['id' => 1, 'exp' => time() + 3600], self::TokenKey, AuthenticationServiceInterface::TokenAlgorithm);
		$this->requestService->method('getRequestBodyDto')->willReturn(new RefreshTokenDto($token));

		$user = UserFixture::getUser(id: 1);
		$this->requestService->method('getUser')->willReturn($user);

		$response = $this->authenticationController->actionPostRefreshToken(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	public function testPostRefreshTokenExpiredReturnsNotAuthorized(): void
	{
		putenv('AUTHORIZATION_TOKEN_KEY=' . self::TokenKey);

		$token = JWT::encode(['id' => 1, 'exp' => time() - 1], self::TokenKey, AuthenticationServiceInterface::TokenAlgorithm);
		$this->requestService->method('getRequestBodyDto')->willReturn(new RefreshTokenDto($token));

		$response = $this->authenticationController->actionPostRefreshToken(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(NotAuthorizedResponse::class, $response);
	}

	public function testPostRefreshTokenInvalidReturnsNotAuthorized(): void
	{
		putenv('AUTHORIZATION_TOKEN_KEY=' . self::TokenKey);

		$this->requestService->method('getRequestBodyDto')->willReturn(new RefreshTokenDto('garbage.token.value'));

		$response = $this->authenticationController->actionPostRefreshToken(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(NotAuthorizedResponse::class, $response);
	}

	public function testPostRefreshTokenUserMismatchReturnsNotAuthorized(): void
	{
		putenv('AUTHORIZATION_TOKEN_KEY=' . self::TokenKey);

		$token = JWT::encode(['id' => 99, 'exp' => time() + 3600], self::TokenKey, AuthenticationServiceInterface::TokenAlgorithm);
		$this->requestService->method('getRequestBodyDto')->willReturn(new RefreshTokenDto($token));

		$user = UserFixture::getUser(id: 1);
		$this->requestService->method('getUser')->willReturn($user);

		$response = $this->authenticationController->actionPostRefreshToken(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(NotAuthorizedResponse::class, $response);
	}

	// --- actionPostSignUp ---

	public function testPostSignUpInvalidPasswordReturnsErrorResponse(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn(
			new SignUpDto('new@example.com', 'weak', 'Test', 1),
		);

		$response = $this->authenticationController->actionPostSignUp(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(ErrorResponse::class, $response);
		self::assertSame(422, $response->getStatusCode());
	}

	public function testPostSignUpEmailExistsReturnsConflict(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn(
			new SignUpDto('existing@example.com', 'Password1!', 'Test', 1),
		);
		$this->userProvider->method('getUserByEmail')->willReturn(UserFixture::getUser());

		$response = $this->authenticationController->actionPostSignUp(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(ConflictResponse::class, $response);
	}

	public function testPostSignUpCurrencyNotFoundReturnsNotFound(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn(
			new SignUpDto('new@example.com', 'Password1!', 'Test', 999),
		);
		$this->userProvider->method('getUserByEmail')->willReturn(null);
		$this->currencyProvider->method('getCurrency')->willReturn(null);

		$response = $this->authenticationController->actionPostSignUp(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testPostSignUpSuccessReturnsJsonResponse(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn(
			new SignUpDto('new@example.com', 'Password1!', 'Test', 1),
		);
		$this->userProvider->method('getUserByEmail')->willReturn(null);
		$this->currencyProvider->method('getCurrency')->willReturn(CurrencyFixture::getCurrency());

		$response = $this->authenticationController->actionPostSignUp(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	// --- actionPostPasswordResetRequest ---

	public function testPostPasswordResetRequestAlwaysReturnsOk(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn(
			new PasswordResetRequestDto('test@example.com'),
		);

		$response = $this->authenticationController->actionPostPasswordResetRequest(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(OkResponse::class, $response);
	}

	// --- actionPostPasswordResetConfirm ---

	public function testPostPasswordResetConfirmTokenNotFoundReturnsNotFound(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn(
			new PasswordResetConfirmDto('nonexistent-token', 'Password1!'),
		);
		$this->passwordResetProvider->method('getPasswordReset')->willReturn(null);

		$response = $this->authenticationController->actionPostPasswordResetConfirm(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testPostPasswordResetConfirmExpiredTokenReturnsNotFound(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn(
			new PasswordResetConfirmDto('expired-token', 'Password1!'),
		);

		$passwordReset = new PasswordReset(
			user: UserFixture::getUser(),
			token: 'expired-token',
			createdAt: new DateTimeImmutable('-25 hours'),
		);
		$this->passwordResetProvider->method('getPasswordReset')->willReturn($passwordReset);

		$response = $this->authenticationController->actionPostPasswordResetConfirm(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testPostPasswordResetConfirmInvalidPasswordReturnsErrorResponse(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn(
			new PasswordResetConfirmDto('valid-token', 'weak'),
		);

		$passwordReset = new PasswordReset(
			user: UserFixture::getUser(),
			token: 'valid-token',
			createdAt: new DateTimeImmutable(),
		);
		$this->passwordResetProvider->method('getPasswordReset')->willReturn($passwordReset);

		$response = $this->authenticationController->actionPostPasswordResetConfirm(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(ErrorResponse::class, $response);
		self::assertSame(422, $response->getStatusCode());
	}

	public function testPostPasswordResetConfirmSuccessReturnsOk(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn(
			new PasswordResetConfirmDto('valid-token', 'Password1!'),
		);

		$passwordReset = new PasswordReset(
			user: UserFixture::getUser(),
			token: 'valid-token',
			createdAt: new DateTimeImmutable(),
		);
		$this->passwordResetProvider->method('getPasswordReset')->willReturn($passwordReset);

		$response = $this->authenticationController->actionPostPasswordResetConfirm(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(OkResponse::class, $response);
	}

	// --- actionPostEmailExists ---

	public function testPostEmailExistsTrueReturnsBoolResponse(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn(
			new EmailExistsDto('existing@example.com'),
		);
		$this->userProvider->method('getUserByEmail')->willReturn(UserFixture::getUser());

		$response = $this->authenticationController->actionPostEmailExists(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(BoolResponse::class, $response);
	}

	public function testPostEmailExistsFalseReturnsBoolResponse(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn(
			new EmailExistsDto('nonexistent@example.com'),
		);
		$this->userProvider->method('getUserByEmail')->willReturn(null);

		$response = $this->authenticationController->actionPostEmailExists(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(BoolResponse::class, $response);
	}

	// --- actionPostGoogleLogin ---

	public function testPostGoogleLoginInvalidTokenReturnsNotAuthorized(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn(
			new GoogleLoginDto('invalid-token'),
		);
		$this->googleAuthService->method('verifyIdToken')->willThrowException(new GoogleAuthException('Invalid'));

		$response = $this->authenticationController->actionPostGoogleLogin(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(NotAuthorizedResponse::class, $response);
	}

	public function testPostGoogleLoginExistingUserByGoogleIdReturnsJsonResponse(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn(
			new GoogleLoginDto('valid-token'),
		);
		$this->googleAuthService->method('verifyIdToken')->willReturn(
			new TokenInfoDto('google-id-123', 'test@example.com', 'Test User', 'aud', true),
		);
		$this->userProvider->method('getUserByGoogleId')->willReturn(UserFixture::getUser());

		$response = $this->authenticationController->actionPostGoogleLogin(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	public function testPostGoogleLoginExistingUserByEmailReturnsJsonResponse(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn(
			new GoogleLoginDto('valid-token'),
		);
		$this->googleAuthService->method('verifyIdToken')->willReturn(
			new TokenInfoDto('google-id-123', 'test@example.com', 'Test User', 'aud', true),
		);
		$this->userProvider->method('getUserByGoogleId')->willReturn(null);
		$this->userProvider->method('getUserByEmail')->willReturn(UserFixture::getUser());

		$response = $this->authenticationController->actionPostGoogleLogin(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	public function testPostGoogleLoginNewUserWithoutCurrencyReturnsRequiresCurrency(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn(
			new GoogleLoginDto('valid-token', null, LocaleEnum::En),
		);
		$this->googleAuthService->method('verifyIdToken')->willReturn(
			new TokenInfoDto('google-id-123', 'new@example.com', 'New User', 'aud', true),
		);
		$this->userProvider->method('getUserByGoogleId')->willReturn(null);
		$this->userProvider->method('getUserByEmail')->willReturn(null);

		$response = $this->authenticationController->actionPostGoogleLogin(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(JsonResponse::class, $response);
		/** @var array{requiresCurrency: bool} $payload */
		$payload = json_decode((string) $response->getBody(), true);
		self::assertTrue($payload['requiresCurrency']);
	}

	public function testPostGoogleLoginNewUserInvalidCurrencyReturnsNotFound(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn(
			new GoogleLoginDto('valid-token', 999, LocaleEnum::En),
		);
		$this->googleAuthService->method('verifyIdToken')->willReturn(
			new TokenInfoDto('google-id-123', 'new@example.com', 'New User', 'aud', true),
		);
		$this->userProvider->method('getUserByGoogleId')->willReturn(null);
		$this->userProvider->method('getUserByEmail')->willReturn(null);
		$this->currencyProvider->method('getCurrency')->willReturn(null);

		$response = $this->authenticationController->actionPostGoogleLogin(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testPostGoogleLoginNewUserValidCurrencyReturnsJsonResponse(): void
	{
		$this->requestService->method('getRequestBodyDto')->willReturn(
			new GoogleLoginDto('valid-token', 1, LocaleEnum::En),
		);
		$this->googleAuthService->method('verifyIdToken')->willReturn(
			new TokenInfoDto('google-id-123', 'new@example.com', 'New User', 'aud', true),
		);
		$this->userProvider->method('getUserByGoogleId')->willReturn(null);
		$this->userProvider->method('getUserByEmail')->willReturn(null);
		$this->currencyProvider->method('getCurrency')->willReturn(CurrencyFixture::getCurrency());
		$this->userProvider->method('createUserFromGoogle')->willReturn(UserFixture::getUser());

		$response = $this->authenticationController->actionPostGoogleLogin(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(JsonResponse::class, $response);
	}
}
