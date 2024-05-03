<?php

declare(strict_types=1);

namespace FinGather\Response;

final class ConflictResponse extends ErrorResponse
{
	/** @param array<string> $headers */
	public function __construct(string $data, int $status = 409, array $headers = [], int $encodingOptions = self::DEFAULT_JSON_FLAGS)
	{
		parent::__construct($data, $status, $headers, $encodingOptions);
	}
}
