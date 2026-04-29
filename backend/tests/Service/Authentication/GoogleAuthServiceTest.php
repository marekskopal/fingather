<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Authentication;

use FinGather\Service\Authentication\Dto\TokenInfoDto;
use FinGather\Service\Authentication\Exceptions\GoogleAuthException;
use FinGather\Service\Authentication\GoogleAuthService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GoogleAuthService::class)]
#[UsesClass(TokenInfoDto::class)]
#[UsesClass(GoogleAuthException::class)]
final class GoogleAuthServiceTest extends TestCase
{
	private const string ClientId = 'test-google-client-id.apps.googleusercontent.com';

	protected function setUp(): void
	{
		putenv('GOOGLE_CLIENT_ID=' . self::ClientId);
	}

	protected function tearDown(): void
	{
		putenv('GOOGLE_CLIENT_ID');
	}

	public function testVerifyIdTokenReturnsTokenInfoOnSuccess(): void
	{
		$service = $this->makeService([
			new Response(200, [], (string) json_encode([
				'sub' => '12345',
				'email' => 'user@example.com',
				'name' => 'Test User',
				'aud' => self::ClientId,
				'email_verified' => 'true',
			])),
		]);

		$result = $service->verifyIdToken('valid-id-token');

		self::assertSame('12345', $result->sub);
		self::assertSame('user@example.com', $result->email);
		self::assertSame('Test User', $result->name);
		self::assertSame(self::ClientId, $result->aud);
		self::assertTrue($result->emailVerified);
	}

	public function testVerifyIdTokenThrowsWhenAudienceMismatches(): void
	{
		$service = $this->makeService([
			new Response(200, [], (string) json_encode([
				'sub' => '12345',
				'email' => 'user@example.com',
				'name' => 'Test User',
				'aud' => 'someone-else.apps.googleusercontent.com',
				'email_verified' => 'true',
			])),
		]);

		$this->expectException(GoogleAuthException::class);
		$this->expectExceptionMessage('Invalid audience in Google ID token');
		$service->verifyIdToken('valid-id-token');
	}

	public function testVerifyIdTokenThrowsWhenEmailNotVerified(): void
	{
		$service = $this->makeService([
			new Response(200, [], (string) json_encode([
				'sub' => '12345',
				'email' => 'user@example.com',
				'name' => 'Test User',
				'aud' => self::ClientId,
				'email_verified' => 'false',
			])),
		]);

		$this->expectException(GoogleAuthException::class);
		$this->expectExceptionMessage('Email not verified with Google');
		$service->verifyIdToken('valid-id-token');
	}

	public function testVerifyIdTokenWrapsHttpFailures(): void
	{
		$service = $this->makeService([
			new ConnectException('Network failure', new Request('GET', 'https://oauth2.googleapis.com/tokeninfo')),
		]);

		$this->expectException(GoogleAuthException::class);
		$this->expectExceptionMessage('Failed to verify Google ID token');
		$service->verifyIdToken('valid-id-token');
	}

	public function testVerifyIdTokenWrapsJsonDecodeErrors(): void
	{
		$service = $this->makeService([
			new Response(200, [], 'this is not json'),
		]);

		$this->expectException(GoogleAuthException::class);
		$this->expectExceptionMessage('Failed to verify Google ID token');
		$service->verifyIdToken('valid-id-token');
	}

	/** @param list<Response|\Throwable> $queuedResponses */
	private function makeService(array $queuedResponses): GoogleAuthService
	{
		$handler = new MockHandler($queuedResponses);
		$stack = HandlerStack::create($handler);
		$client = new Client(['handler' => $stack]);

		return new GoogleAuthService($client);
	}
}
