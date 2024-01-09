<?php

declare(strict_types=1);

namespace FinGather\Middleware;

use FinGather\Middleware\Exception\NotAuthorizedException;
use FinGather\Route\Routes;
use FinGather\Service\Authentication\AuthenticationService;
use FinGather\Service\Provider\UserProvider;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AuthorizationMiddleware implements MiddlewareInterface
{
	public const AttributeToken = 'token';
	public const AttributeUser = 'user';

	private const AuthHeader = 'Authorization';
	private const AuthHeaderType = 'Bearer ';

	public function __construct(private readonly UserProvider $userProvider)
	{
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		if (in_array(
			$request->getUri()->getPath(),
			[Routes::Health->value, Routes::AuthenticationLogin->value, Routes::AuthenticationSignUp->value],
			strict: true,
		)) {
			return $handler->handle($request);
		}

		if ($request->getMethod() === 'OPTIONS') {
			return $handler->handle($request);
		}

		$authorizationHeader = $request->getHeader(self::AuthHeader)[0] ?? null;

		if ($authorizationHeader === null) {
			throw new NotAuthorizedException('Authorization header not found', $request);
		}

		if (!str_starts_with($authorizationHeader, self::AuthHeaderType)) {
			throw new NotAuthorizedException('Authorization header is not Bearer type', $request);
		}

		$jwtToken = substr($authorizationHeader, strlen(self::AuthHeaderType));

		try {
			$token = JWT::decode($jwtToken, new Key((string) getenv('AUTHORIZATION_TOKEN_KEY'), AuthenticationService::TokenAlgorithm));
		} catch (\Throwable $exception) {
			throw new NotAuthorizedException($exception->getMessage(), $request, 401, $exception);
		}

		$user = $this->userProvider->getUser($token->id);
		if ($user === null) {
			throw new NotAuthorizedException('User is not authorized.', $request);
		}

		$request = $request->withAttribute(self::AttributeToken, $jwtToken)
			->withAttribute(self::AttributeUser, $user);

		return $handler->handle($request);
	}
}
