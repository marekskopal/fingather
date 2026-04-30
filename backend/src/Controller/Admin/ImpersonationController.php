<?php

declare(strict_types=1);

namespace FinGather\Controller\Admin;

use FinGather\Dto\ImpersonationSessionDto;
use FinGather\Model\Entity\Enum\UserRoleEnum;
use FinGather\Response\ErrorResponse;
use FinGather\Response\NotFoundResponse;
use FinGather\Route\Routes;
use FinGather\Service\Authentication\AuthenticationServiceInterface;
use FinGather\Service\Provider\ImpersonationSessionProviderInterface;
use FinGather\Service\Provider\UserProviderInterface;
use FinGather\Service\Request\RequestServiceInterface;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteGet;
use MarekSkopal\Router\Attribute\RoutePost;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ImpersonationController
{
	public function __construct(
		private RequestServiceInterface $requestService,
		private UserProviderInterface $userProvider,
		private AuthenticationServiceInterface $authenticationService,
		private ImpersonationSessionProviderInterface $impersonationSessionProvider,
	) {
	}

	#[RoutePost(Routes::AdminUserImpersonate->value)]
	public function actionPostImpersonate(ServerRequestInterface $request, int $userId): ResponseInterface
	{
		if ($userId < 1) {
			return new NotFoundResponse('User id is required.');
		}

		$admin = $this->requestService->getUser($request);
		$target = $this->userProvider->getUser($userId);

		if ($target === null) {
			return new NotFoundResponse('User with id "' . $userId . '" was not found.');
		}

		if ($target->id === $admin->id) {
			return new ErrorResponse('Cannot impersonate yourself.', 403);
		}

		if ($target->role === UserRoleEnum::Admin) {
			return new ErrorResponse('Cannot impersonate another administrator.', 403);
		}

		$serverParams = $request->getServerParams();
		$remoteAddr = $serverParams['REMOTE_ADDR'] ?? null;
		$ipAddress = is_string($remoteAddr) ? $remoteAddr : '';
		$userAgent = $request->getHeader('User-Agent')[0] ?? '';

		$session = $this->impersonationSessionProvider->createSession(
			admin: $admin,
			target: $target,
			ipAddress: $ipAddress,
			userAgent: $userAgent,
		);

		$dto = $this->authenticationService->createImpersonationAuthentication(admin: $admin, target: $target, sessionId: $session->id);

		return new JsonResponse($dto);
	}

	#[RouteGet(Routes::AdminImpersonationSessions->value)]
	public function actionGetSessions(ServerRequestInterface $request): ResponseInterface
	{
		$sessions = [];
		foreach ($this->impersonationSessionProvider->getRecentSessions(100) as $session) {
			$sessions[] = ImpersonationSessionDto::fromEntity($session);
		}

		return new JsonResponse($sessions);
	}
}
