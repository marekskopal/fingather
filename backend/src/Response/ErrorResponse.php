<?php

declare(strict_types=1);

namespace FinGather\Response;

use Laminas\Diactoros\Response\JsonResponse;

class ErrorResponse extends JsonResponse
{
	/** @param array<non-empty-string, array<string>|string> $headers */
	public function __construct(string $data, int $status = 500, array $headers = [], int $encodingOptions = self::DEFAULT_JSON_FLAGS)
	{
		parent::__construct(
			[
				'code' => $status,
				'message' => $data,
			],
			$status,
			$headers,
			$encodingOptions,
		);
	}

	public static function fromException(\Throwable $exception): self
	{
		$code = $exception->getCode() >= 100 && $exception->getCode() <= 999 ? (int) $exception->getCode() : 500;
		$message = $exception->getMessage();
		if ($message === '') {
			$message = 'Internal Server Error';
		}

		return new self($message, $code);
	}
}
