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
	#[TestWith(['MYSQL_PASSWORD'])]
	#[TestWith(['MYSQL_ROOT_PASSWORD'])]
	#[TestWith(['RABBITMQ_PASSWORD'])]
	#[TestWith(['REDIS_PASSWORD'])]
	#[TestWith(['SMTP_PASSWORD'])]
	#[TestWith(['STRIPE_SECRET_KEY'])]
	#[TestWith(['AUTHORIZATION_TOKEN_KEY'])]
	#[TestWith(['ENCRYPTION_KEY'])]
	#[TestWith(['TWELVEDATA_API_KEY'])]
	#[TestWith(['HTTP_AUTHORIZATION'])]
	#[TestWith(['token'])]
	#[TestWith(['password'])]
	public function testMasksSecretKeys(string $key): void
	{
		self::assertTrue(Logger::isSensitiveKey($key));
	}

	#[TestWith(['GOOGLE_CLIENT_ID'])]
	#[TestWith(['MYSQL_HOST'])]
	#[TestWith(['MYSQL_USER'])]
	#[TestWith(['REQUEST_URI'])]
	#[TestWith(['portfolioId'])]
	#[TestWith(['email'])]
	#[TestWith(['id'])]
	public function testLeavesNonSecretKeysVisible(string $key): void
	{
		self::assertFalse(Logger::isSensitiveKey($key));
	}

	public function testIntegerKeysAreNeverSensitive(): void
	{
		self::assertFalse(Logger::isSensitiveKey(0));
		self::assertFalse(Logger::isSensitiveKey(42));
	}
}
