<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\EmailVerifyCreateDto;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\EmailVerifyProvider;
use FinGather\Service\Provider\UserProvider;
use FinGather\Service\Request\RequestService;
use MarekSkopal\Router\Attribute\RoutePost;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class EmailVerifyController
{
	public function __construct(
		private readonly EmailVerifyProvider $emailVerifyProvider,
		private readonly UserProvider $userProvider,
		private readonly RequestService $requestService,
	)
	{
	}

	#[RoutePost(Routes::EmailVerify->value)]
	public function actionPostEmailVerify(ServerRequestInterface $request): ResponseInterface
	{
		$emailVerifyCreateDto = $this->requestService->getRequestBodyDto($request, EmailVerifyCreateDto::class);

		$emailVerify = $this->emailVerifyProvider->getEmailVerify($emailVerifyCreateDto->token);
		if ($emailVerify === null) {
			return new NotFoundResponse('Email Verify with token "' . $emailVerifyCreateDto->token . '" was not found.');
		}

		$this->userProvider->emailVerifyUser($emailVerify->user);

		return new OkResponse();
	}
}
