<?php

declare(strict_types=1);

namespace FinGather\Controller\Admin;

use FinGather\Middleware\Exception\NotAuthorizedException;
use FinGather\Model\Entity\Enum\UserRoleEnum;
use FinGather\Service\Request\RequestService;
use Psr\Http\Message\ServerRequestInterface;

abstract class AdminController
{
	public function __construct(protected readonly RequestService $requestService)
	{
	}

	protected function checkAdminRole(ServerRequestInterface $request): void
	{
		$user = $this->requestService->getUser($request);

		if ($user->getRole() === UserRoleEnum::Admin) {
			return;
		}

		throw new NotAuthorizedException('User is not authorized.', $request);
	}
}
