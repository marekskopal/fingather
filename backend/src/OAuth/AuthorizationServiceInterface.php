<?php

declare(strict_types=1);

namespace FinGather\OAuth;

use FinGather\Model\Entity\User;

interface AuthorizationServiceInterface
{
	public function createAuthorizationCode(
		string $clientId,
		int $userId,
		string $codeChallenge,
		string $codeChallengeMethod,
		string $redirectUri,
	): string;

	public function exchangeCode(string $code, string $codeVerifier, string $clientId, string $redirectUri): OAuthTokenPair;

	public function refreshToken(string $refreshToken, string $clientId): OAuthTokenPair;

	public function validateAccessToken(string $accessToken): User;
}
