<?php

declare(strict_types=1);

namespace FinGather\Response;

use Laminas\Diactoros\Response\JsonResponse;

class OkResponse extends JsonResponse
{
	/** @param array<string> $headers */
	public function __construct(
		string $data = 'Ok',
		int $status = 200,
		array $headers = [],
		int $encodingOptions = self::DEFAULT_JSON_FLAGS
	)
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
		$code = $exception->getCode() >= 100 && $exception->getCode() <= 999 ? $exception->getCode() : 500;
		$message = $exception->getMessage() ?: 'Internal Server Error';

		return new self($message, $code);
	}
}
