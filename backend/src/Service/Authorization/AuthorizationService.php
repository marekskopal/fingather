<?php

declare(strict_types=1);

namespace FinGather\Service\Authorization;

use FinGather\Dto\AuthorizationDto;
use FinGather\Dto\CredentialsDto;
use FinGather\Model\Repository\UserRepository;
use FinGather\Service\Authorization\Exceptions\AuthorizationException;
use Firebase\JWT\JWT;

final class AuthorizationService
{
	public const TokenAlgorithm = 'HS256';

	public function __construct(private readonly UserRepository $userRepository)
	{
	}

	public function authorize(CredentialsDto $credential): AuthorizationDto
	{
		$user = $this->userRepository->findUserByEmail($credential->email);
		if ($user === null) {
			throw new AuthorizationException('User with email ' . $credential->email . ' was not found.');
		}

		if (!password_verify($credential->password, $user->getPassword())) {
			throw new AuthorizationException('Password is incorrect.');
		}

		$expiration = time() + 3600;

		return new AuthorizationDto(
			token: $this->createToken($user->getId(), $expiration),
			tokenExpirationTime: $expiration,
			id: $user->getId(),
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
