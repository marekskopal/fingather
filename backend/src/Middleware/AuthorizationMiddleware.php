<?php

declare(strict_types=1);

namespace FinGather\Middleware;

use FinGather\Middleware\Exception\NotAuthorizedException;
use FinGather\Route\Routes;
use FinGather\Service\Authentication\AuthenticationService;
use FinGather\Service\Provider\UserProvider;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;

final class AuthorizationMiddleware implements MiddlewareInterface
{
	/** @api */
	public const string AttributeToken = 'token';
	public const string AttributeUser = 'user';

	public const string AuthHeader = 'Authorization';
	public const string AuthHeaderType = 'Bearer ';

	public function __construct(private readonly UserProvider $userProvider)
	{
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		if (in_array(
			$request->getUri()->getPath(),
			[
				Routes::Health->value,
				Routes::AuthenticationLogin->value,
				Routes::AuthenticationSignUp->value,
				Routes::AuthenticationEmailExists->value,
				Routes::AuthenticationGoogleClientId->value,
				Routes::AuthenticationGoogleLogin->value,
				Routes::EmailVerify->value,
				Routes::Currencies->value,
			],
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
			/** @var object{id: int}&stdClass $token */
			$token = JWT::decode($jwtToken, new Key((string) getenv('AUTHORIZATION_TOKEN_KEY'), AuthenticationService::TokenAlgorithm));
		} catch (ExpiredException $exception) {
			if ($request->getUri()->getPath() !== Routes::AuthenticationRefreshToken->value) {
				throw new NotAuthorizedException('AccessToken is expired.', $request, 401, $exception);
			}

			/** @var object{id: int} $payload */
			$payload = $exception->getPayload();
			$request = $this->withUserAttribute($request, $payload->id);

			return $handler->handle($request);
		} catch (\Throwable $exception) {
			throw new NotAuthorizedException('AccessToken is expired.', $request, 401, $exception);
		}

		$request = $this->withUserAttribute($request, $token->id);

		$request = $request->withAttribute(self::AttributeToken, $jwtToken);

		return $handler->handle($request);
	}

	private function withUserAttribute(ServerRequestInterface $request, ?int $userId): ServerRequestInterface
	{
		if ($userId === null) {
			throw new NotAuthorizedException('User is not authorized.', $request);
		}

		$user = $this->userProvider->getUser($userId);
		if ($user === null) {
			throw new NotAuthorizedException('User is not authorized.', $request);
		}

		return $request->withAttribute(self::AttributeUser, $user);
	}
}
