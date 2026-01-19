<?php

declare(strict_types=1);

namespace FinGather\Service\Authentication\Exceptions;

final class GoogleAuthException extends \Exception
{
	/** @param array{sub?: string, email?: string, name?: string, aud?: string, email_verified?: string}|null $payload */
	public function __construct(string $message = '', int $code = 0, private readonly ?array $payload = null, ?\Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

	/**
	 * @return array{sub?: string, email?: string, name?: string, aud?: string, email_verified?: string}|null $payload
	 * @api
	 */
	public function getPayload(): ?array
	{
		return $this->payload;
	}
}
