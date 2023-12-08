<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\CredentialsDto;
use FinGather\Service\Authorization\AuthorizationService;
use FinGather\Service\Authorization\Exceptions\AuthorizationException;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function Safe\json_decode;

class AuthenticationController
{
	public function __construct(private readonly AuthorizationService $authorizationService,)
	{
	}

	public function actionPostLogin(ServerRequestInterface $request): ResponseInterface
	{
		/** @var array{email: string, password:string} $requestBody*/
		$requestBody = json_decode($request->getBody()->getContents(), assoc: true);

		$credentials = new CredentialsDto($requestBody['email'], $requestBody['password']);

		try {
			return new JsonResponse($this->authorizationService->authorize($credentials));
		} catch (AuthorizationException) {
			return new JsonResponse('Email or password id invalid.', 401);
		}
	}
}
