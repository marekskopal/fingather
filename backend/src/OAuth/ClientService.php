<?php

declare(strict_types=1);

namespace FinGather\OAuth;

use DateTimeImmutable;
use FinGather\Model\Entity\OAuthClient;
use FinGather\Model\Repository\OAuthClientRepository;

use const JSON_THROW_ON_ERROR;

final readonly class ClientService implements ClientServiceInterface
{
	public function __construct(private OAuthClientRepository $oAuthClientRepository)
	{
	}

	public function findByClientId(string $clientId): ?OAuthClient
	{
		return $this->oAuthClientRepository->findByClientId($clientId);
	}

	public function validateRedirectUri(string $clientId, string $redirectUri): bool
	{
		$client = $this->oAuthClientRepository->findByClientId($clientId);
		if ($client === null) {
			return false;
		}

		/** @var list<string> $allowedUris */
		$allowedUris = json_decode($client->redirectUris, true, 2, JSON_THROW_ON_ERROR);

		foreach ($allowedUris as $allowedUri) {
			if ($this->matchesRedirectUri($allowedUri, $redirectUri)) {
				return true;
			}
		}

		return false;
	}

	/** @param list<string> $redirectUris */
	public function registerClient(string $clientName, array $redirectUris): OAuthClient
	{
		$client = new OAuthClient(
			clientId: bin2hex(random_bytes(16)),
			clientName: $clientName,
			redirectUris: json_encode($redirectUris, JSON_THROW_ON_ERROR),
			user: null,
			createdAt: new DateTimeImmutable(),
		);

		$this->oAuthClientRepository->persist($client);

		return $client;
	}

	private function matchesRedirectUri(string $allowedUri, string $requestedUri): bool
	{
		$allowedParsed = parse_url($allowedUri);
		$requestedParsed = parse_url($requestedUri);

		if ($allowedParsed === false || $requestedParsed === false) {
			return false;
		}

		$allowedHost = $allowedParsed['host'] ?? '';
		$requestedHost = $requestedParsed['host'] ?? '';

		// Allow localhost with any port per OAuth 2.1
		if (
			in_array($allowedHost, ['localhost', '127.0.0.1', '::1'], true)
			&& $allowedHost === $requestedHost
			&& ($allowedParsed['scheme'] ?? '') === ($requestedParsed['scheme'] ?? '')
			&& ($allowedParsed['path'] ?? '/') === ($requestedParsed['path'] ?? '/')
		) {
			return true;
		}

		return $allowedUri === $requestedUri;
	}
}
