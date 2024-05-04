<?php

declare(strict_types=1);

namespace FinGather\Middleware\Exception;

use Psr\Http\Message\RequestInterface;

final class NotAuthorizedException extends \RuntimeException
{
	public function __construct(string $message, private readonly RequestInterface $request, int $code = 401, ?\Throwable $previous = null,)
	{
		parent::__construct($message, $code, $previous);
	}

	/** @api */
	public function getRequest(): RequestInterface
	{
		return $this->request;
	}
}
