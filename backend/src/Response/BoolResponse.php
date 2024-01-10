<?php

declare(strict_types=1);

namespace FinGather\Response;

use Laminas\Diactoros\Response\JsonResponse;

class BoolResponse extends JsonResponse
{
	/** @param array<string> $headers */
	public function __construct(bool $data, int $status = 200, array $headers = [], int $encodingOptions = self::DEFAULT_JSON_FLAGS,)
	{
		parent::__construct(
			[
				'value' => $data,
			],
			$status,
			$headers,
			$encodingOptions,
		);
	}
}
