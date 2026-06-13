<?php

declare(strict_types=1);

namespace FinGather\Service\Logger;

use Psr\Log\LoggerInterface;
use Tracy\Bridges\Psr\TracyToPsrLoggerAdapter;
use Tracy\Debugger;
use const E_ALL;

final readonly class Logger
{
	/**
	 * Substrings (case-insensitive) marking a dumped key as secret. Matched against array keys and
	 * object property names so credentials never reach the bluescreen logs — covering env vars such as
	 * *_PASSWORD, *_SECRET_KEY, ENCRYPTION_KEY, *_API_KEY and the HTTP_AUTHORIZATION server param.
	 */
	private const SensitiveKeyPattern = '~pass|pwd|secret|token|_key|apikey|api_key|private|credential|salt|signature|authorization|cvv|creditcard~i';

	public static function initLogger(?string $directory): LoggerInterface
	{
		// @see https://github.com/nette/tracy/issues/280
		Debugger::setLogger(new TracyLogger($directory));

		mb_internal_encoding('UTF-8');
		error_reporting(E_ALL);
		// RoadRunner logger collects stderr output
		ini_set('display_errors', 'stderr');
		// Force Tracy to send error response in php-cli mode
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'roadrunner/sapi';
		// Convert E_WARNING to \ErrorException
		Debugger::$strictMode = true;
		Debugger::enable(true, $directory);

		// Mask secret values in logged bluescreens (request server params, env vars, tokens, …).
		Debugger::getBlueScreen()->scrubber = self::isSensitiveKey(...);

		return new TracyToPsrLoggerAdapter(Debugger::getLogger());
	}

	/**
	 * @api Wired as Tracy's bluescreen scrubber callback (signature: key, value, class).
	 */
	public static function isSensitiveKey(int|string $key, mixed $value = null, ?string $class = null): bool
	{
		return is_string($key) && preg_match(self::SensitiveKeyPattern, $key) === 1;
	}
}
