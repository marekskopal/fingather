<?php

declare(strict_types=1);

namespace FinGather\Service\Request;

use FinGather\Middleware\AuthorizationMiddleware;
use FinGather\Model\Entity\User;
use Psr\Http\Message\ServerRequestInterface;

final class RequestService implements RequestServiceInterface
{
	public function getUser(ServerRequestInterface $request): User
	{
		$user = $request->getAttribute(AuthorizationMiddleware::AttributeUser);
		assert($user instanceof User);
		return $user;
	}
}
