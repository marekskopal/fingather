<?php

declare(strict_types=1);

namespace FinGather\Service\Logger;

use ErrorException;
use Tracy\Helpers;
use Tracy\Logger;
use const FILE_APPEND;
use const PHP_EOL;

final class TracyLogger extends Logger
{
	public static function formatLogLine(mixed $message, ?string $exceptionFile = null): string
	{
		return implode(' ', [
			date('[Y-m-d H-i-s]'),
			preg_replace('#\s*\r?\n\s*#', ' ', self::formatMessage($message)),
			' @  ' . Helpers::getSource(),
			$exceptionFile !== null ? ' @@  ' . basename($exceptionFile) : null,
		]) . ($message instanceof \Throwable ? PHP_EOL . strstr((string) $message, 'Stack trace:') : '');
	}

	public function log(mixed $message, string $level = self::INFO): ?string
	{
		if ($level === self::DEBUG && getenv('BACKEND_LOG_LEVEL') !== 'debug') {
			return null;
		}

		/** @see \Tracy\Bridges\Psr\TracyToPsrLoggerAdapter::log() */
		if (is_array($message) && isset($message['exception']) && $message['exception'] instanceof \Throwable) {
			$context = $message['context'] ?? [];
			//@phpstan-ignore-next-line
			$message = $message['exception'];
		}

		$exceptionFile = parent::log($message, $level);

		set_error_handler(
			function (int $severity, string $message, ?string $file, ?int $line): void {
				throw new ErrorException($message, $severity, $severity, $file, $line);
			},
		);

		try {
			$log = self::formatLogLine($message, $exceptionFile);
			$log = str_replace(' @@ ', json_encode($context ?? []) . ' @@ ', $log);
			file_put_contents('php://stderr', $log, FILE_APPEND);
		} finally {
			restore_error_handler();
		}

		return $exceptionFile;
	}
}
