<?php

declare(strict_types=1);

namespace FinGather\OAuth;

use FinGather\Model\Entity\OAuthAuthorization;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\OAuthAuthorizationRepository;
use FinGather\Service\Provider\UserProviderInterface;
use RuntimeException;

final readonly class AuthorizationService implements AuthorizationServiceInterface
{
	private const int AccessTokenLifetime = 3600;

	private const int RefreshTokenLifetime = 2592000;

	private const int CodeLifetime = 60;

	public function __construct(
		private OAuthAuthorizationRepository $oAuthAuthorizationRepository,
		private PkceVerifier $pkceVerifier,
		private ClientServiceInterface $clientService,
		private UserProviderInterface $userProvider,
	) {
	}

	public function createAuthorizationCode(
		string $clientId,
		int $userId,
		string $codeChallenge,
		string $codeChallengeMethod,
		string $redirectUri,
	): string {
		$client = $this->clientService->findByClientId($clientId);
		if ($client === null) {
			throw new RuntimeException('Unknown client');
		}

		$user = $this->userProvider->getUser($userId);
		if ($user === null) {
			throw new RuntimeException('Unknown user');
		}

		$code = bin2hex(random_bytes(32));

		$authorization = new OAuthAuthorization(
			clientId: $clientId,
			user: $user,
			authorizationCodeHash: hash('sha256', $code),
			codeChallenge: $codeChallenge,
			codeChallengeMethod: $codeChallengeMethod,
			redirectUri: $redirectUri,
			codeExpires: time() + self::CodeLifetime,
		);

		$this->oAuthAuthorizationRepository->persist($authorization);

		return $code;
	}

	public function exchangeCode(string $code, string $codeVerifier, string $clientId, string $redirectUri): OAuthTokenPair
	{
		$codeHash = hash('sha256', $code);

		$authorization = $this->oAuthAuthorizationRepository->findByAuthorizationCodeHash($codeHash);
		if ($authorization === null) {
			throw new RuntimeException('Invalid authorization code');
		}

		if ($authorization->revoked) {
			throw new RuntimeException('Authorization code has been revoked');
		}

		if ($authorization->codeExpires !== null && $authorization->codeExpires < time()) {
			throw new RuntimeException('Authorization code has expired');
		}

		if ($authorization->clientId !== $clientId) {
			throw new RuntimeException('Client ID mismatch');
		}

		if ($authorization->redirectUri !== $redirectUri) {
			throw new RuntimeException('Redirect URI mismatch');
		}

		if ($authorization->codeChallenge === null || !$this->pkceVerifier->verify($codeVerifier, $authorization->codeChallenge)) {
			throw new RuntimeException('PKCE verification failed');
		}

		$tokenPair = $this->issueTokenPair($clientId, $authorization->user);

		// Revoke the authorization code after successful exchange
		$authorization->authorizationCodeHash = null;
		$authorization->revoked = true;
		$this->oAuthAuthorizationRepository->persist($authorization);

		return $tokenPair;
	}

	public function refreshToken(string $refreshToken, string $clientId): OAuthTokenPair
	{
		$refreshTokenHash = hash('sha256', $refreshToken);

		$authorization = $this->oAuthAuthorizationRepository->findByRefreshTokenHash($refreshTokenHash);
		if ($authorization === null) {
			throw new RuntimeException('Invalid refresh token');
		}

		if ($authorization->revoked) {
			throw new RuntimeException('Refresh token has been revoked');
		}

		if ($authorization->refreshTokenExpires !== null && $authorization->refreshTokenExpires < time()) {
			throw new RuntimeException('Refresh token has expired');
		}

		if ($authorization->clientId !== $clientId) {
			throw new RuntimeException('Client ID mismatch');
		}

		// Revoke old token
		$authorization->revoked = true;
		$this->oAuthAuthorizationRepository->persist($authorization);

		return $this->issueTokenPair($clientId, $authorization->user);
	}

	public function validateAccessToken(string $accessToken): User
	{
		$accessTokenHash = hash('sha256', $accessToken);

		$authorization = $this->oAuthAuthorizationRepository->findByAccessTokenHash($accessTokenHash);
		if ($authorization === null) {
			throw new RuntimeException('Invalid access token');
		}

		if ($authorization->revoked) {
			throw new RuntimeException('Access token has been revoked');
		}

		if ($authorization->accessTokenExpires !== null && $authorization->accessTokenExpires < time()) {
			throw new RuntimeException('Access token has expired');
		}

		return $authorization->user;
	}

	private function issueTokenPair(string $clientId, User $user): OAuthTokenPair
	{
		$accessToken = bin2hex(random_bytes(32));
		$refreshToken = bin2hex(random_bytes(32));

		$authorization = new OAuthAuthorization(
			clientId: $clientId,
			user: $user,
			accessTokenHash: hash('sha256', $accessToken),
			refreshTokenHash: hash('sha256', $refreshToken),
			accessTokenExpires: time() + self::AccessTokenLifetime,
			refreshTokenExpires: time() + self::RefreshTokenLifetime,
		);

		$this->oAuthAuthorizationRepository->persist($authorization);

		return new OAuthTokenPair(accessToken: $accessToken, refreshToken: $refreshToken, expiresIn: self::AccessTokenLifetime);
	}
}
