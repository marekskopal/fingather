<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\AuthorizationDto;
use FinGather\Service\Provider\PortfolioProvider;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthenticationController
{
	public function actionPostLogin(ServerRequestInterface $request): ResponseInterface
	{
		return new JsonResponse(new AuthorizationDto(
			JWT::encode(['id' => 1], '123456789', 'HS256'),
			time() + 86400,
			1,
		));
	}
}
