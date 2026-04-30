<?php

declare(strict_types=1);

namespace FinGather\Middleware;

use FinGather\Middleware\Exception\NotAuthorizedException;
use FinGather\Model\Entity\Enum\ImpersonationTerminationReasonEnum;
use FinGather\Model\Entity\Enum\UserRoleEnum;
use FinGather\Route\Routes;
use FinGather\Service\Authentication\AuthenticationServiceInterface;
use FinGather\Service\Authentication\ImpersonationContext;
use FinGather\Service\Authentication\ImpersonationDenylist;
use FinGather\Service\Provider\ImpersonationSessionProviderInterface;
use FinGather\Service\Provider\UserProviderInterface;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;

final readonly class AuthorizationMiddleware implements MiddlewareInterface
{
	/** @api */
	public const string AttributeToken = 'token';
	public const string AttributeUser = 'user';
	public const string AttributeImpersonator = 'impersonator';

	public const string AuthHeader = 'Authorization';
	public const string AuthHeaderType = 'Bearer ';

	private const array OpenRoutes = [
		Routes::Health->value,
		Routes::AuthenticationLogin->value,
		Routes::AuthenticationSignUp->value,
		Routes::PasswordResetRequest->value,
		Routes::PasswordResetConfirm->value,
		Routes::AuthenticationEmailExists->value,
		Routes::AuthenticationGoogleClientId->value,
		Routes::AuthenticationGoogleLogin->value,
		Routes::EmailVerify->value,
		Routes::Currencies->value,
		Routes::Mcp->value,
		Routes::OAuthMetadata->value,
		Routes::OAuthResourceMetadata->value,
		Routes::OAuthToken->value,
		Routes::OAuthRegister->value,
		Routes::OAuthClientInfo->value,
	];

	public function __construct(
		private UserProviderInterface $userProvider,
		private ImpersonationSessionProviderInterface $impersonationSessionProvider,
		private ImpersonationContext $impersonationContext,
	) {
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$this->impersonationContext->deactivate();

		if (in_array($request->getUri()->getPath(), self::OpenRoutes, strict: true)) {
			return $handler->handle($request);
		}

		if ($request->getMethod() === 'OPTIONS') {
			return $handler->handle($request);
		}

		$jwtToken = $this->extractToken($request);

		try {
			/** @var object{id: int, imp?: int, sid?: int, typ?: string}&stdClass $token */
			$token = JWT::decode(
				$jwtToken,
				new Key((string) getenv('AUTHORIZATION_TOKEN_KEY'), AuthenticationServiceInterface::TokenAlgorithm),
			);
		} catch (ExpiredException $exception) {
			return $this->handleExpiredToken($request, $handler, $exception);
		} catch (\Throwable $exception) {
			throw new NotAuthorizedException('AccessToken is invalid.', $request, 401, $exception);
		}

		if (($token->typ ?? null) === AuthenticationServiceInterface::TokenTypeImpersonation) {
			return $this->handleImpersonation($request, $handler, $token, $jwtToken);
		}

		$request = $this->withUserAttribute($request, $token->id);
		$request = $request->withAttribute(self::AttributeToken, $jwtToken);

		return $handler->handle($request);
	}

	private function extractToken(ServerRequestInterface $request): string
	{
		$authorizationHeader = $request->getHeader(self::AuthHeader)[0] ?? null;

		if ($authorizationHeader === null) {
			throw new NotAuthorizedException('Authorization header not found', $request);
		}

		if (!str_starts_with($authorizationHeader, self::AuthHeaderType)) {
			throw new NotAuthorizedException('Authorization header is not Bearer type', $request);
		}

		return substr($authorizationHeader, strlen(self::AuthHeaderType));
	}

	private function handleExpiredToken(
		ServerRequestInterface $request,
		RequestHandlerInterface $handler,
		ExpiredException $exception,
	): ResponseInterface {
		if ($request->getUri()->getPath() !== Routes::AuthenticationRefreshToken->value) {
			throw new NotAuthorizedException('AccessToken is expired.', $request, 401, $exception);
		}

		/** @var object{id: int, typ?: string} $payload */
		$payload = $exception->getPayload();

		if (($payload->typ ?? null) === AuthenticationServiceInterface::TokenTypeImpersonation) {
			throw new NotAuthorizedException('Impersonation tokens cannot be refreshed.', $request, 401, $exception);
		}

		$request = $this->withUserAttribute($request, $payload->id);

		return $handler->handle($request);
	}

	/** @param object{id: int, imp?: int, sid?: int, typ?: string}&stdClass $token */
	private function handleImpersonation(
		ServerRequestInterface $request,
		RequestHandlerInterface $handler,
		object $token,
		string $jwtToken,
	): ResponseInterface {
		if (!isset($token->imp, $token->sid)) {
			throw new NotAuthorizedException('Invalid impersonation token.', $request);
		}

		$session = $this->impersonationSessionProvider->getActiveSession($token->sid);
		if ($session === null) {
			throw new NotAuthorizedException('Impersonation session is no longer active.', $request);
		}

		$admin = $this->userProvider->getUser($token->imp);
		if ($admin === null || $admin->role !== UserRoleEnum::Admin) {
			$this->impersonationSessionProvider->endSession($session, ImpersonationTerminationReasonEnum::AdminRoleRevoked);

			throw new NotAuthorizedException('Impersonator is no longer authorised.', $request);
		}

		$this->guardImpersonationRoute($request);

		$this->impersonationContext->activate($admin->id, $session->id);

		$request = $this->withUserAttribute($request, $token->id);
		$request = $request->withAttribute(self::AttributeImpersonator, $admin);
		$request = $request->withAttribute(self::AttributeToken, $jwtToken);

		return $handler->handle($request);
	}

	private function guardImpersonationRoute(ServerRequestInterface $request): void
	{
		$path = $request->getUri()->getPath();

		if ($path === Routes::AuthenticationRefreshToken->value) {
			throw new NotAuthorizedException('Impersonation tokens cannot be refreshed.', $request, 401);
		}

		if ($path === Routes::AuthenticationStopImpersonation->value) {
			return;
		}

		if (str_starts_with($path, '/api/admin/')) {
			throw new NotAuthorizedException('Admin endpoints are blocked during impersonation.', $request, 403);
		}

		if (ImpersonationDenylist::isBlocked($request->getMethod(), $path)) {
			throw new NotAuthorizedException('This action is blocked during impersonation.', $request, 403);
		}
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

		if (str_starts_with($request->getUri()->getPath(), '/api/admin/')) {
			if ($user->role !== UserRoleEnum::Admin) {
				throw new NotAuthorizedException('User is not authorized.', $request);
			}
		}

		return $request->withAttribute(self::AttributeUser, $user);
	}
}
