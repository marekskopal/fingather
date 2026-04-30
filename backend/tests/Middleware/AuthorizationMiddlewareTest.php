<?php

declare(strict_types=1);

namespace FinGather\Tests\Middleware;

use DateTimeImmutable;
use FinGather\Middleware\AuthorizationMiddleware;
use FinGather\Middleware\Exception\NotAuthorizedException;
use FinGather\Model\Entity\Enum\UserRoleEnum;
use FinGather\Model\Entity\ImpersonationSession;
use FinGather\Model\Entity\User;
use FinGather\Service\Authentication\AuthenticationServiceInterface;
use FinGather\Service\Authentication\ImpersonationContext;
use FinGather\Service\Authentication\ImpersonationDenylist;
use FinGather\Service\Provider\ImpersonationSessionProviderInterface;
use FinGather\Service\Provider\UserProviderInterface;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use Firebase\JWT\JWT;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

#[CoversClass(AuthorizationMiddleware::class)]
#[UsesClass(User::class)]
#[UsesClass(ImpersonationSession::class)]
#[UsesClass(ImpersonationContext::class)]
#[UsesClass(ImpersonationDenylist::class)]
#[UsesClass(NotAuthorizedException::class)]
final class AuthorizationMiddlewareTest extends TestCase
{
	private const string TokenKey = 'test-secret-key-long-enough-for-hs256-algorithm';

	protected function setUp(): void
	{
		putenv('AUTHORIZATION_TOKEN_KEY=' . self::TokenKey);
	}

	protected function tearDown(): void
	{
		putenv('AUTHORIZATION_TOKEN_KEY');
	}

	private function makeImpersonationToken(int $targetId, int $adminId, int $sessionId, int $expOffset = 600): string
	{
		return JWT::encode(
			[
				'id' => $targetId,
				'exp' => time() + $expOffset,
				AuthenticationServiceInterface::ClaimImpersonator => $adminId,
				AuthenticationServiceInterface::ClaimSessionId => $sessionId,
				AuthenticationServiceInterface::ClaimType => AuthenticationServiceInterface::TokenTypeImpersonation,
			],
			self::TokenKey,
			AuthenticationServiceInterface::TokenAlgorithm,
		);
	}

	private function makeRequest(string $method, string $path, string $token): ServerRequestInterface
	{
		$request = new ServerRequest(
			serverParams: [],
			uploadedFiles: [],
			uri: 'http://localhost' . $path,
			method: $method,
		);

		return $request->withHeader('Authorization', 'Bearer ' . $token);
	}

	private function makeHandler(): CapturingHandler
	{
		return new CapturingHandler();
	}

	private function makeMiddleware(
		User $admin,
		User $target,
		ImpersonationSession $session,
		?ImpersonationSession $activeSession = null,
	): AuthorizationMiddleware {
		$userProvider = self::createStub(UserProviderInterface::class);
		$userProvider->method('getUser')->willReturnCallback(
			static fn (int $id): ?User => match ($id) {
				$admin->id => $admin,
				$target->id => $target,
				default => null,
			},
		);

		$sessionProvider = self::createStub(ImpersonationSessionProviderInterface::class);
		$sessionProvider->method('getActiveSession')->willReturn($activeSession ?? $session);

		return new AuthorizationMiddleware(
			$userProvider,
			$sessionProvider,
			new ImpersonationContext(),
		);
	}

	private function makeSession(User $admin, User $target, int $id = 1): ImpersonationSession
	{
		$session = new ImpersonationSession(
			adminUser: $admin,
			targetUser: $target,
			startedAt: new DateTimeImmutable(),
			endedAt: null,
			ipAddress: '',
			userAgent: '',
			terminationReason: null,
		);
		$session->id = $id;
		return $session;
	}

	public function testAllowsImpersonationOnRegularEndpoint(): void
	{
		$admin = UserFixture::getUser(id: 1, role: UserRoleEnum::Admin);
		$target = UserFixture::getUser(id: 2, email: 'target@example.com', role: UserRoleEnum::User);
		$session = $this->makeSession($admin, $target);

		$middleware = $this->makeMiddleware($admin, $target, $session);
		$handler = $this->makeHandler();

		$token = $this->makeImpersonationToken($target->id, $admin->id, $session->id);
		$request = $this->makeRequest('GET', '/api/portfolios', $token);

		$response = $middleware->process($request, $handler);

		self::assertSame(200, $response->getStatusCode());
		self::assertNotNull($handler->capturedRequest);
		$user = $handler->capturedRequest->getAttribute(AuthorizationMiddleware::AttributeUser);
		self::assertInstanceOf(User::class, $user);
		self::assertSame($target->id, $user->id);

		$impersonator = $handler->capturedRequest->getAttribute(AuthorizationMiddleware::AttributeImpersonator);
		self::assertInstanceOf(User::class, $impersonator);
		self::assertSame($admin->id, $impersonator->id);
	}

	public function testBlocksAdminEndpointDuringImpersonation(): void
	{
		$admin = UserFixture::getUser(id: 1, role: UserRoleEnum::Admin);
		$target = UserFixture::getUser(id: 2, role: UserRoleEnum::User);
		$session = $this->makeSession($admin, $target);

		$middleware = $this->makeMiddleware($admin, $target, $session);
		$token = $this->makeImpersonationToken($target->id, $admin->id, $session->id);
		$request = $this->makeRequest('GET', '/api/admin/user', $token);

		$this->expectException(NotAuthorizedException::class);
		$middleware->process($request, $this->makeHandler());
	}

	public function testBlocksDenylistedRoutes(): void
	{
		$admin = UserFixture::getUser(id: 1, role: UserRoleEnum::Admin);
		$target = UserFixture::getUser(id: 2, role: UserRoleEnum::User);
		$session = $this->makeSession($admin, $target);

		$middleware = $this->makeMiddleware($admin, $target, $session);
		$token = $this->makeImpersonationToken($target->id, $admin->id, $session->id);
		$request = $this->makeRequest('DELETE', '/api/current-user', $token);

		$this->expectException(NotAuthorizedException::class);
		$middleware->process($request, $this->makeHandler());
	}

	public function testRefreshTokenEndpointRejectsImpersonationToken(): void
	{
		$admin = UserFixture::getUser(id: 1, role: UserRoleEnum::Admin);
		$target = UserFixture::getUser(id: 2, role: UserRoleEnum::User);
		$session = $this->makeSession($admin, $target);

		$middleware = $this->makeMiddleware($admin, $target, $session);
		$token = $this->makeImpersonationToken($target->id, $admin->id, $session->id);
		$request = $this->makeRequest('POST', '/api/authentication/refresh-token', $token);

		$this->expectException(NotAuthorizedException::class);
		$middleware->process($request, $this->makeHandler());
	}

	public function testAllowsStopImpersonationEndpoint(): void
	{
		$admin = UserFixture::getUser(id: 1, role: UserRoleEnum::Admin);
		$target = UserFixture::getUser(id: 2, role: UserRoleEnum::User);
		$session = $this->makeSession($admin, $target);

		$middleware = $this->makeMiddleware($admin, $target, $session);
		$handler = $this->makeHandler();

		$token = $this->makeImpersonationToken($target->id, $admin->id, $session->id);
		$request = $this->makeRequest('POST', '/api/authentication/stop-impersonation', $token);

		$response = $middleware->process($request, $handler);

		self::assertSame(200, $response->getStatusCode());
		self::assertNotNull($handler->capturedRequest);
		self::assertInstanceOf(
			User::class,
			$handler->capturedRequest->getAttribute(AuthorizationMiddleware::AttributeImpersonator),
		);
	}

	public function testRejectsWhenImpersonatorRoleRevoked(): void
	{
		$demoted = UserFixture::getUser(id: 1, role: UserRoleEnum::User);
		$target = UserFixture::getUser(id: 2, role: UserRoleEnum::User);
		$session = $this->makeSession($demoted, $target);

		$middleware = $this->makeMiddleware($demoted, $target, $session);
		$token = $this->makeImpersonationToken($target->id, $demoted->id, $session->id);
		$request = $this->makeRequest('GET', '/api/portfolios', $token);

		$this->expectException(NotAuthorizedException::class);
		$middleware->process($request, $this->makeHandler());
	}

	public function testRejectsWhenSessionEnded(): void
	{
		$admin = UserFixture::getUser(id: 1, role: UserRoleEnum::Admin);
		$target = UserFixture::getUser(id: 2, role: UserRoleEnum::User);
		$session = $this->makeSession($admin, $target);

		$userProvider = self::createStub(UserProviderInterface::class);
		$userProvider->method('getUser')->willReturnCallback(
			static fn (int $id): User => $id === $admin->id ? $admin : $target,
		);
		$sessionProvider = self::createStub(ImpersonationSessionProviderInterface::class);
		$sessionProvider->method('getActiveSession')->willReturn(null);

		$middleware = new AuthorizationMiddleware(
			$userProvider,
			$sessionProvider,
			new ImpersonationContext(),
		);

		$token = $this->makeImpersonationToken($target->id, $admin->id, $session->id);
		$request = $this->makeRequest('GET', '/api/portfolios', $token);

		$this->expectException(NotAuthorizedException::class);
		$middleware->process($request, $this->makeHandler());
	}

	public function testNormalAdminTokenAccessesAdminEndpoint(): void
	{
		$admin = UserFixture::getUser(id: 1, role: UserRoleEnum::Admin);
		$target = UserFixture::getUser(id: 2, role: UserRoleEnum::User);
		$session = $this->makeSession($admin, $target);

		$middleware = $this->makeMiddleware($admin, $target, $session);
		$handler = $this->makeHandler();

		$token = JWT::encode(
			['id' => $admin->id, 'exp' => time() + 600],
			self::TokenKey,
			AuthenticationServiceInterface::TokenAlgorithm,
		);
		$request = $this->makeRequest('GET', '/api/admin/user', $token);

		$response = $middleware->process($request, $handler);
		self::assertSame(200, $response->getStatusCode());
	}

	public function testNonAdminUserBlockedFromAdminEndpoint(): void
	{
		$admin = UserFixture::getUser(id: 1, role: UserRoleEnum::Admin);
		$target = UserFixture::getUser(id: 2, role: UserRoleEnum::User);
		$session = $this->makeSession($admin, $target);

		$middleware = $this->makeMiddleware($admin, $target, $session);
		$token = JWT::encode(
			['id' => $target->id, 'exp' => time() + 600],
			self::TokenKey,
			AuthenticationServiceInterface::TokenAlgorithm,
		);
		$request = $this->makeRequest('GET', '/api/admin/user', $token);

		$this->expectException(NotAuthorizedException::class);
		$middleware->process($request, $this->makeHandler());
	}
}
