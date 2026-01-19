<?php

declare(strict_types=1);

namespace FinGather\Service\Authentication;

use FinGather\Service\Authentication\Dto\TokenInfoDto;
use FinGather\Service\Authentication\Exceptions\GoogleAuthException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use const JSON_THROW_ON_ERROR;

final readonly class GoogleAuthService
{
	private const string TokenInfoUrl = 'https://oauth2.googleapis.com/tokeninfo';

	public function __construct(private Client $httpClient)
	{
	}

	public function verifyIdToken(string $idToken): TokenInfoDto
	{
		try {
			$response = $this->httpClient->get(self::TokenInfoUrl, [
				'query' => ['id_token' => $idToken],
			]);

			/** @var array{sub: string, email: string, name: string, aud: string, email_verified: string} $payload */
			$payload = json_decode($response->getBody()->getContents(), true, flags: JSON_THROW_ON_ERROR);

			$tokenInfo = TokenInfoDto::fromArray($payload);
		} catch (GuzzleException | \JsonException $e) {
			throw new GoogleAuthException('Failed to verify Google ID token: ' . $e->getMessage(), previous: $e);
		}

		$expectedClientId = (string) getenv('GOOGLE_CLIENT_ID');
		if ($tokenInfo->aud !== $expectedClientId) {
			throw new GoogleAuthException('Invalid audience in Google ID token', payload: $payload);
		}

		if (!$tokenInfo->emailVerified) {
			throw new GoogleAuthException('Email not verified with Google', payload: $payload);
		}

		return $tokenInfo;
	}
}
