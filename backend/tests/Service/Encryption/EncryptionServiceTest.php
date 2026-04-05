<?php

declare(strict_types=1);

namespace FinGather\Tests\Service\Encryption;

use FinGather\Service\Encryption\EncryptionService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(EncryptionService::class)]
final class EncryptionServiceTest extends TestCase
{
	private const string EncryptionKey = 'test-encryption-key-32-chars-min';

	#[TestWith(['short-api-key'])]
	#[TestWith([''])]
	#[TestWith(['a'])]
	#[TestWith(['special chars: !@#$%^&*()_+-=[]{}|;:,.<>?'])]
	#[TestWith(["line1\nline2\ttab"])]
	public function testEncryptDecryptRoundTrip(string $plaintext): void
	{
		$service = new EncryptionService(self::EncryptionKey);

		$encrypted = $service->encrypt($plaintext);
		$decrypted = $service->decrypt($encrypted);

		self::assertSame($plaintext, $decrypted);
	}

	public function testEncryptedValueDiffersFromPlaintext(): void
	{
		$service = new EncryptionService(self::EncryptionKey);
		$plaintext = 'my-secret-api-key';

		$encrypted = $service->encrypt($plaintext);

		self::assertNotSame($plaintext, $encrypted);
	}

	public function testEncryptProducesDifferentCiphertextEachTime(): void
	{
		$service = new EncryptionService(self::EncryptionKey);
		$plaintext = 'my-secret-api-key';

		$encrypted1 = $service->encrypt($plaintext);
		$encrypted2 = $service->encrypt($plaintext);

		self::assertNotSame($encrypted1, $encrypted2);
	}

	public function testDecryptWithWrongKeyThrows(): void
	{
		$service = new EncryptionService(self::EncryptionKey);
		$encrypted = $service->encrypt('my-secret-api-key');

		$wrongKeyService = new EncryptionService('wrong-encryption-key-32-chars-mn');

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Failed to decrypt data.');
		$wrongKeyService->decrypt($encrypted);
	}

	public function testDecryptWithCorruptedDataThrows(): void
	{
		$service = new EncryptionService(self::EncryptionKey);

		$this->expectException(\RuntimeException::class);
		$service->decrypt(base64_encode('corrupted-data'));
	}

	public function testDecryptWithInvalidBase64Throws(): void
	{
		$service = new EncryptionService(self::EncryptionKey);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Failed to decode encrypted data.');
		$service->decrypt('not-valid-base64!!!');
	}
}
