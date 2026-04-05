<?php

declare(strict_types=1);

namespace FinGather\Service\Encryption;

use SensitiveParameter;
use const OPENSSL_RAW_DATA;

final readonly class EncryptionService implements EncryptionServiceInterface
{
	private const string Cipher = 'aes-256-gcm';
	private const int TagLength = 16;

	public function __construct(#[SensitiveParameter] private string $encryptionKey)
	{
	}

	public function encrypt(#[SensitiveParameter] string $plaintext): string
	{
		$ivLength = openssl_cipher_iv_length(self::Cipher);
		$iv = random_bytes(max(1, $ivLength));

		$tag = '';
		$ciphertext = openssl_encrypt($plaintext, self::Cipher, $this->encryptionKey, OPENSSL_RAW_DATA, $iv, $tag, '', self::TagLength);
		if ($ciphertext === false) {
			throw new \RuntimeException('Failed to encrypt data.');
		}

		return base64_encode($iv . $tag . $ciphertext);
	}

	public function decrypt(#[SensitiveParameter] string $encrypted): string
	{
		$data = base64_decode($encrypted, true);
		if ($data === false) {
			throw new \RuntimeException('Failed to decode encrypted data.');
		}

		$ivLength = openssl_cipher_iv_length(self::Cipher);

		$iv = substr($data, 0, $ivLength);
		$tag = substr($data, $ivLength, self::TagLength);
		$ciphertext = substr($data, $ivLength + self::TagLength);

		$plaintext = openssl_decrypt($ciphertext, self::Cipher, $this->encryptionKey, OPENSSL_RAW_DATA, $iv, $tag);
		if ($plaintext === false) {
			throw new \RuntimeException('Failed to decrypt data.');
		}

		return $plaintext;
	}
}
