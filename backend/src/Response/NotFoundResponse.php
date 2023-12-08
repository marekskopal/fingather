<?php

namespace FinGather\Response;

class NotFoundResponse extends AErrorResponse
{
	/** @param array<string> $headers */
	public function __construct(string $data, int $status = 401, array $headers = [], int $encodingOptions = self::DEFAULT_JSON_FLAGS)
	{
		parent::__construct($data, $status, $headers, $encodingOptions);
	}
}