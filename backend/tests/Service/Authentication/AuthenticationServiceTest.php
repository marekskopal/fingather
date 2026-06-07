<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Authentication;

use FinGather\Dto\AuthenticationDto;
use FinGather\Dto\CredentialsDto;
use FinGather\Dto\ImpersonationAuthenticationDto;
use FinGather\Middleware\AuthorizationMiddleware;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\UserRoleEnum;
use FinGather\Model\Entity\User;
use FinGather\Service\Authentication\AuthenticationService;
use FinGather\Service\Authentication\AuthenticationServiceInterface;
use FinGather\Service\Authentication\Exceptions\AuthenticationException;
use FinGather\Service\Provider\UserProviderInterface;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use const PASSWORD_BCRYPT;

#[CoversClass(AuthenticationService::class)]
#[UsesClass(AuthenticationDto::class)]
#[UsesClass(CredentialsDto::class)]
#[UsesClass(ImpersonationAuthenticationDto::class)]
#[UsesClass(Currency::class)]
#[UsesClass(User::class)]
final class AuthenticationServiceTest extends TestCase
{
	private const string TokenKey = 'test-secret-key-long-enough-for-hs256-algorithm';

	protected function setUp(): void
	{
		putenv('AUTHORIZATION_TOKEN_KEY=' . self::TokenKey);
	}

	protected function tearDown(): void
	{
		putenv('AUTHORIZATION_TOKEN_KEY');
		putenv('IMPERSONATION_TOKEN_EXPIRATION');
	}

	public function testAuthenticateThrowsWhenUserNotFound(): void
	{
		$userProvider = self::createStub(UserProviderInterface::class);
		$userProvider->method('getUserByEmail')->willReturn(null);

		$service = new AuthenticationService($userProvider);

		$this->expectException(AuthenticationException::class);
		$this->expectExceptionMessageIsOrContains('User with email missing@example.com was not found.');
		$service->authenticate(new CredentialsDto('missing@example.com', 'whatever'));
	}

	public function testAuthenticateThrowsWhenPasswordMissing(): void
	{
		$user = UserFixture::getUser(password: null);

		$userProvider = self::createStub(UserProviderInterface::class);
		$userProvider->method('getUserByEmail')->willReturn($user);

		$service = new AuthenticationService($userProvider);

		$this->expectException(AuthenticationException::class);
		$this->expectExceptionMessageIsOrContains('Password is incorrect.');
		$service->authenticate(new CredentialsDto($user->email, 'anything'));
	}

	public function testAuthenticateThrowsWhenPasswordIncorrect(): void
	{
		$user = UserFixture::getUser(password: password_hash('correct', PASSWORD_BCRYPT));

		$userProvider = self::createStub(UserProviderInterface::class);
		$userProvider->method('getUserByEmail')->willReturn($user);

		$service = new AuthenticationService($userProvider);

		$this->expectException(AuthenticationException::class);
		$this->expectExceptionMessageIsOrContains('Password is incorrect.');
		$service->authenticate(new CredentialsDto($user->email, 'wrong'));
	}

	public function testAuthenticateReturnsTokensOnSuccess(): void
	{
		$user = UserFixture::getUser(id: 42, password: password_hash('correct', PASSWORD_BCRYPT));

		$userProvider = self::createStub(UserProviderInterface::class);
		$userProvider->method('getUserByEmail')->willReturn($user);

		$service = new AuthenticationService($userProvider);
		$result = $service->authenticate(new CredentialsDto($user->email, 'correct'));

		self::assertSame(42, $result->userId);
		self::assertNotSame('', $result->accessToken);
		self::assertNotSame('', $result->refreshToken);

		// Tokens are valid JWTs signed with the configured key.
		$decoded = JWT::decode($result->accessToken, new Key(self::TokenKey, AuthenticationServiceInterface::TokenAlgorithm));
		self::assertSame(42, $decoded->id);
		self::assertGreaterThan(time(), $decoded->exp);
	}

	public function testCreateAuthenticationIssuesAccessAndRefreshTokensWithDifferentExpirations(): void
	{
		$user = UserFixture::getUser(id: 7);

		$userProvider = self::createStub(UserProviderInterface::class);
		$service = new AuthenticationService($userProvider);

		$result = $service->createAuthentication($user);

		$accessClaims = JWT::decode($result->accessToken, new Key(self::TokenKey, AuthenticationServiceInterface::TokenAlgorithm));
		$refreshClaims = JWT::decode($result->refreshToken, new Key(self::TokenKey, AuthenticationServiceInterface::TokenAlgorithm));

		self::assertSame(7, $accessClaims->id);
		self::assertSame(7, $refreshClaims->id);
		// Refresh token must outlive the access token (604800s vs 3600s).
		self::assertGreaterThan($accessClaims->exp, $refreshClaims->exp);
	}

	public function testAddAuthenticationHeaderWritesBearerHeaderOnRequest(): void
	{
		$user = UserFixture::getUser(id: 5);

		$userProvider = self::createStub(UserProviderInterface::class);
		$service = new AuthenticationService($userProvider);

		$capturedHeaderName = null;
		$capturedHeaderValue = null;
		$request = self::createStub(ServerRequestInterface::class);
		$request->method('withHeader')->willReturnCallback(
			function (string $name, mixed $value) use (&$capturedHeaderName, &$capturedHeaderValue, $request): ServerRequestInterface {
				$capturedHeaderName = $name;
				$capturedHeaderValue = $value;
				return $request;
			},
		);

		$service->addAuthenticationHeader($request, $user);

		self::assertSame(AuthorizationMiddleware::AuthHeader, $capturedHeaderName);
		self::assertIsString($capturedHeaderValue);
		self::assertStringStartsWith(AuthorizationMiddleware::AuthHeaderType, $capturedHeaderValue);

		$token = substr($capturedHeaderValue, strlen(AuthorizationMiddleware::AuthHeaderType));
		$decoded = JWT::decode($token, new Key(self::TokenKey, AuthenticationServiceInterface::TokenAlgorithm));
		self::assertSame(5, $decoded->id);
	}

	public function testCreateImpersonationAuthenticationEncodesImpAndSidClaims(): void
	{
		$admin = UserFixture::getUser(id: 1, role: UserRoleEnum::Admin);
		$target = UserFixture::getUser(id: 5, email: 'target@example.com', role: UserRoleEnum::User);

		$userProvider = self::createStub(UserProviderInterface::class);
		$service = new AuthenticationService($userProvider);

		$dto = $service->createImpersonationAuthentication($admin, $target, sessionId: 99);

		self::assertSame(99, $dto->sessionId);
		self::assertSame($target->id, $dto->targetUserId);
		self::assertSame('target@example.com', $dto->targetUserEmail);

		$decoded = JWT::decode($dto->accessToken, new Key(self::TokenKey, AuthenticationServiceInterface::TokenAlgorithm));
		self::assertSame($target->id, $decoded->id);
		self::assertSame($admin->id, $decoded->{AuthenticationServiceInterface::ClaimImpersonator});
		self::assertSame(99, $decoded->{AuthenticationServiceInterface::ClaimSessionId});
		self::assertSame(AuthenticationServiceInterface::TokenTypeImpersonation, $decoded->{AuthenticationServiceInterface::ClaimType});
		self::assertGreaterThan(time(), $decoded->exp);
	}

	public function testGetImpersonationTokenExpirationDefaultsTo1800(): void
	{
		$userProvider = self::createStub(UserProviderInterface::class);
		$service = new AuthenticationService($userProvider);

		self::assertSame(1800, $service->getImpersonationTokenExpiration());
	}

	public function testGetImpersonationTokenExpirationHonorsEnvOverride(): void
	{
		putenv('IMPERSONATION_TOKEN_EXPIRATION=5');

		$userProvider = self::createStub(UserProviderInterface::class);
		$service = new AuthenticationService($userProvider);

		self::assertSame(5, $service->getImpersonationTokenExpiration());
	}
}
