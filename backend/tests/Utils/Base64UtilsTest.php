<?php

declare(strict_types=1);

namespace FinGather\Tests\Utils;

use FinGather\Utils\Base64Utils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Base64Utils::class)]
final class Base64UtilsTest extends TestCase
{
	public function testEncodeReturnsStandardBase64(): void
	{
		self::assertSame('SGVsbG8sIFdvcmxkIQ==', Base64Utils::encode('Hello, World!'));
	}

	public function testDecodeRoundtripsPlainBase64(): void
	{
		self::assertSame('Hello, World!', Base64Utils::decode('SGVsbG8sIFdvcmxkIQ=='));
	}

	public function testDecodeStripsDataUrlPrefix(): void
	{
		// Mirrors the logic for `data:image/png;base64,...` payloads from clients.
		self::assertSame('Hello, World!', Base64Utils::decode('data:image/png;base64,SGVsbG8sIFdvcmxkIQ=='));
	}

	public function testDecodeReturnsEmptyStringOnInvalidStrictInput(): void
	{
		// strict: true makes base64_decode return false on invalid characters; cast yields ''.
		self::assertSame('', Base64Utils::decode('not valid base64!!!'));
	}
}
