<?php

declare(strict_types=1);

namespace FinGather\Tests\OAuth;

use FinGather\OAuth\PkceVerifier;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PkceVerifier::class)]
final class PkceVerifierTest extends TestCase
{
	public function testVerifyValidChallenge(): void
	{
		$verifier = new PkceVerifier();

		$codeVerifier = 'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk';
		$codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

		self::assertTrue($verifier->verify($codeVerifier, $codeChallenge));
	}

	public function testVerifyInvalidChallenge(): void
	{
		$verifier = new PkceVerifier();

		self::assertFalse($verifier->verify('valid-verifier', 'invalid-challenge'));
	}

	public function testVerifyMismatchedVerifier(): void
	{
		$verifier = new PkceVerifier();

		$codeVerifier = 'correct-verifier';
		$codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

		self::assertFalse($verifier->verify('wrong-verifier', $codeChallenge));
	}
}
