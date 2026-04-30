<?php

declare(strict_types=1);

namespace FinGather\Tests\Controller\Admin;

use DateTimeImmutable;
use FinGather\Controller\Admin\ImpersonationController;
use FinGather\Dto\ImpersonationAuthenticationDto;
use FinGather\Dto\ImpersonationSessionDto;
use FinGather\Model\Entity\Enum\UserRoleEnum;
use FinGather\Model\Entity\ImpersonationSession;
use FinGather\Model\Entity\User;
use FinGather\Response\ErrorResponse;
use FinGather\Response\NotFoundResponse;
use FinGather\Service\Authentication\AuthenticationServiceInterface;
use FinGather\Service\Provider\ImpersonationSessionProviderInterface;
use FinGather\Service\Provider\UserProviderInterface;
use FinGather\Service\Request\RequestServiceInterface;
use FinGather\Tests\Fixtures\Model\Entity\UserFixture;
use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

#[CoversClass(ImpersonationController::class)]
#[UsesClass(User::class)]
#[UsesClass(ImpersonationSession::class)]
#[UsesClass(NotFoundResponse::class)]
#[UsesClass(ErrorResponse::class)]
#[UsesClass(ImpersonationAuthenticationDto::class)]
#[UsesClass(ImpersonationSessionDto::class)]
final class ImpersonationControllerTest extends TestCase
{
	private RequestServiceInterface&Stub $requestService;

	private UserProviderInterface&Stub $userProvider;

	private AuthenticationServiceInterface&Stub $authenticationService;

	private ImpersonationSessionProviderInterface&Stub $impersonationSessionProvider;

	private ImpersonationController $controller;

	private User $admin;

	protected function setUp(): void
	{
		$this->admin = UserFixture::getUser(id: 1, role: UserRoleEnum::Admin);

		$this->requestService = $this::createStub(RequestServiceInterface::class);
		$this->requestService->method('getUser')->willReturn($this->admin);

		$this->userProvider = $this::createStub(UserProviderInterface::class);
		$this->authenticationService = $this::createStub(AuthenticationServiceInterface::class);
		$this->impersonationSessionProvider = $this::createStub(ImpersonationSessionProviderInterface::class);

		$this->controller = new ImpersonationController(
			$this->requestService,
			$this->userProvider,
			$this->authenticationService,
			$this->impersonationSessionProvider,
		);
	}

	public function testImpersonateRejectsInvalidUserId(): void
	{
		$response = $this->controller->actionPostImpersonate(
			$this::createStub(ServerRequestInterface::class),
			0,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testImpersonateRejectsUnknownTarget(): void
	{
		$this->userProvider->method('getUser')->willReturn(null);

		$response = $this->controller->actionPostImpersonate(
			$this::createStub(ServerRequestInterface::class),
			42,
		);

		self::assertInstanceOf(NotFoundResponse::class, $response);
	}

	public function testImpersonateRejectsSelf(): void
	{
		$this->userProvider->method('getUser')->willReturn($this->admin);

		$response = $this->controller->actionPostImpersonate(
			$this::createStub(ServerRequestInterface::class),
			$this->admin->id,
		);

		self::assertInstanceOf(ErrorResponse::class, $response);
		self::assertSame(403, $response->getStatusCode());
	}

	public function testImpersonateRejectsAdminTarget(): void
	{
		$otherAdmin = UserFixture::getUser(id: 7, role: UserRoleEnum::Admin);
		$this->userProvider->method('getUser')->willReturn($otherAdmin);

		$response = $this->controller->actionPostImpersonate(
			$this::createStub(ServerRequestInterface::class),
			$otherAdmin->id,
		);

		self::assertInstanceOf(ErrorResponse::class, $response);
		self::assertSame(403, $response->getStatusCode());
	}

	public function testImpersonateSuccessReturnsJsonResponse(): void
	{
		$target = UserFixture::getUser(id: 5, role: UserRoleEnum::User);
		$this->userProvider->method('getUser')->willReturn($target);

		$session = new ImpersonationSession(
			adminUser: $this->admin,
			targetUser: $target,
			startedAt: new DateTimeImmutable(),
			endedAt: null,
			ipAddress: '127.0.0.1',
			userAgent: 'phpunit',
			terminationReason: null,
		);
		$session->id = 99;

		$this->impersonationSessionProvider->method('createSession')->willReturn($session);
		$this->authenticationService->method('createImpersonationAuthentication')->willReturn(
			new ImpersonationAuthenticationDto(
				accessToken: 'mock-access-token',
				expiresAt: time() + 1800,
				sessionId: 99,
				targetUserId: $target->id,
				targetUserEmail: $target->email,
				targetUserName: $target->name,
			),
		);

		$request = $this::createStub(ServerRequestInterface::class);
		$request->method('getServerParams')->willReturn(['REMOTE_ADDR' => '127.0.0.1']);
		$request->method('getHeader')->willReturn(['phpunit']);

		$response = $this->controller->actionPostImpersonate($request, $target->id);

		self::assertInstanceOf(JsonResponse::class, $response);
	}

	public function testGetSessionsReturnsJsonResponse(): void
	{
		$target = UserFixture::getUser(id: 5, role: UserRoleEnum::User);
		$session = new ImpersonationSession(
			adminUser: $this->admin,
			targetUser: $target,
			startedAt: new DateTimeImmutable(),
			endedAt: null,
			ipAddress: '127.0.0.1',
			userAgent: 'phpunit',
			terminationReason: null,
		);
		$session->id = 1;

		$this->impersonationSessionProvider->method('getRecentSessions')->willReturn([$session]);

		$response = $this->controller->actionGetSessions(
			$this::createStub(ServerRequestInterface::class),
		);

		self::assertInstanceOf(JsonResponse::class, $response);
	}
}
