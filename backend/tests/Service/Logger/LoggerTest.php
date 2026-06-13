<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Logger;

use FinGather\Service\Logger\Logger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(Logger::class)]
final class LoggerTest extends TestCase
{
	// Secret env vars / attributes that leaked into bluescreen logs before masking.
	#[TestWith(['MYSQL_PASSWORD', true])]
	#[TestWith(['MYSQL_ROOT_PASSWORD', true])]
	#[TestWith(['RABBITMQ_PASSWORD', true])]
	#[TestWith(['REDIS_PASSWORD', true])]
	#[TestWith(['SMTP_PASSWORD', true])]
	#[TestWith(['STRIPE_SECRET_KEY', true])]
	#[TestWith(['AUTHORIZATION_TOKEN_KEY', true])]
	#[TestWith(['ENCRYPTION_KEY', true])]
	#[TestWith(['TWELVEDATA_API_KEY', true])]
	#[TestWith(['HTTP_AUTHORIZATION', true])]
	#[TestWith(['token', true])]
	#[TestWith(['password', true])]
	// Non-secret keys must stay visible for debugging.
	#[TestWith(['GOOGLE_CLIENT_ID', false])]
	#[TestWith(['MYSQL_HOST', false])]
	#[TestWith(['MYSQL_USER', false])]
	#[TestWith(['REQUEST_URI', false])]
	#[TestWith(['portfolioId', false])]
	#[TestWith(['email', false])]
	#[TestWith(['id', false])]
	public function testIsSensitiveKey(string $key, bool $expected): void
	{
		self::assertSame($expected, Logger::isSensitiveKey($key));
	}

	public function testIntegerKeysAreNeverSensitive(): void
	{
		self::assertFalse(Logger::isSensitiveKey(0));
		self::assertFalse(Logger::isSensitiveKey(42));
	}
}
