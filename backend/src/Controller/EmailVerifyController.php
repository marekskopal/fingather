<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Service\Provider\EmailVerifyProvider;
use FinGather\Service\Provider\UserProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function Safe\json_decode;

class EmailVerifyController
{
	public function __construct(private readonly EmailVerifyProvider $emailVerifyProvider, private readonly UserProvider $userProvider,)
	{
	}

	public function actionPostEmailVerify(ServerRequestInterface $request): ResponseInterface
	{
		/** @var array{token: string} $requestBody */
		$requestBody = json_decode($request->getBody()->getContents(), assoc: true);

		$emailVerify = $this->emailVerifyProvider->getEmailVerify($requestBody['token']);
		if ($emailVerify === null) {
			return new NotFoundResponse('Email Verify with token "' . $requestBody['token'] . '" was not found.');
		}

		$this->userProvider->emailVerifyUser($emailVerify->getUser());

		return new OkResponse();
	}
}
