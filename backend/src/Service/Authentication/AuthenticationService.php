<?php

declare(strict_types=1);

namespace FinGather\Service\Authentication;

use FinGather\Dto\AuthenticationDto;
use FinGather\Dto\CredentialsDto;
use FinGather\Middleware\AuthorizationMiddleware;
use FinGather\Model\Entity\User;
use FinGather\Service\Authentication\Exceptions\AuthenticationException;
use FinGather\Service\Provider\UserProvider;
use Firebase\JWT\JWT;
use Psr\Http\Message\ServerRequestInterface;

final class AuthenticationService
{
	public const TokenAlgorithm = 'HS256';

	private const AccessTokenExpiration = 3600;
	private const RefreshTokenExpiration = 604800;

	public function __construct(private readonly UserProvider $userProvider)
	{
	}

	public function authenticate(CredentialsDto $credential): AuthenticationDto
	{
		$user = $this->userProvider->getUserByEmail($credential->email);
		if ($user === null) {
			throw new AuthenticationException('User with email ' . $credential->email . ' was not found.');
		}

		if ($user->password === null || !password_verify($credential->password, $user->password)) {
			throw new AuthenticationException('Password is incorrect.');
		}

		$this->userProvider->updateLastLoggedIn($user);

		return $this->createAuthentication($user);
	}

	public function createAuthentication(User $user): AuthenticationDto
	{
		$accessTokenExpiration = time() + self::AccessTokenExpiration;
		$refreshTokenExpiration = time() + self::RefreshTokenExpiration;

		$this->userProvider->updateLastRefreshTokenGenerated($user);

		return new AuthenticationDto(
			accessToken: $this->createToken($user->id, $accessTokenExpiration),
			refreshToken: $this->createToken($user->id, $refreshTokenExpiration),
			userId: $user->id,
		);
	}

	public function addAuthenticationHeader(ServerRequestInterface $request, User $user): ServerRequestInterface
	{
		return $request->withHeader(
			AuthorizationMiddleware::AuthHeader,
			AuthorizationMiddleware::AuthHeaderType . $this->createAuthentication($user)->accessToken,
		);
	}

	private function createToken(int $userId, int $exp): string
	{
		$key = (string) getenv('AUTHORIZATION_TOKEN_KEY');

		return JWT::encode([
			'id' => $userId,
			'exp' => $exp,
		], $key, self::TokenAlgorithm);
	}
}
