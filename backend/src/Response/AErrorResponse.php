<?php

namespace FinGather\Response;

use App\Acl\Response\ErrorSchema;
use Laminas\Diactoros\Response\JsonResponse;

abstract class AErrorResponse extends JsonResponse
{
	/** @param array<string> $headers */
	public function __construct(
		string $data,
		int $status = 500,
		array $headers = [],
		int $encodingOptions = self::DEFAULT_JSON_FLAGS,
	) {
		parent::__construct(
			[
				'code' =>$status,
				'message' => $data
			],
			$status,
			$headers,
			$encodingOptions,
		);
	}
}