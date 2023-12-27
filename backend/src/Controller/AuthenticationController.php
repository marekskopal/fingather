<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\CredentialsDto;
use FinGather\Service\Authentication\AuthenticationService;
use FinGather\Service\Authentication\Exceptions\AuthenticationException;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function Safe\json_decode;

class AuthenticationController
{
	public function __construct(private readonly AuthenticationService $authenticationService,)
	{
	}

	public function actionPostLogin(ServerRequestInterface $request): ResponseInterface
	{
		/** @var array{email: string, password:string} $requestBody*/
		$requestBody = json_decode($request->getBody()->getContents(), assoc: true);

		$credentials = new CredentialsDto($requestBody['email'], $requestBody['password']);

		try {
			return new JsonResponse($this->authenticationService->authenticate($credentials));
		} catch (AuthenticationException) {
			return new JsonResponse('Email or password id invalid.', 401);
		}
	}
}
