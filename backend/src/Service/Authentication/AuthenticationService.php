<?php

declare(strict_types=1);

namespace FinGather\Service\Authentication;

use FinGather\Dto\AuthenticationDto;
use FinGather\Dto\CredentialsDto;
use FinGather\Service\Authentication\Exceptions\AuthenticationException;
use FinGather\Service\Provider\UserProvider;
use Firebase\JWT\JWT;

final class AuthenticationService
{
	public const TokenAlgorithm = 'HS256';

	public function __construct(private readonly UserProvider $userProvider)
	{
	}

	public function authenticate(CredentialsDto $credential): AuthenticationDto
	{
		$user = $this->userProvider->getUserByEmail($credential->email);
		if ($user === null) {
			throw new AuthenticationException('User with email ' . $credential->email . ' was not found.');
		}

		if (!password_verify($credential->password, $user->getPassword())) {
			throw new AuthenticationException('Password is incorrect.');
		}

		$expiration = time() + 3600;

		return new AuthenticationDto(
			token: $this->createToken($user->getId(), $expiration),
			tokenExpirationTime: $expiration,
			userId: $user->getId(),
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
