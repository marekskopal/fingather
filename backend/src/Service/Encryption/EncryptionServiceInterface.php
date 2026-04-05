<?php

declare(strict_types=1);

namespace FinGather\Service\Encryption;

use SensitiveParameter;

interface EncryptionServiceInterface
{
	public function encrypt(#[SensitiveParameter] string $plaintext): string;

	public function decrypt(#[SensitiveParameter] string $encrypted): string;
}
