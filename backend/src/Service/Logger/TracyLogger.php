<?php

declare(strict_types=1);

namespace FinGather\Service\Logger;

use ErrorException;
use Tracy\Helpers;
use Tracy\Logger;
use function Safe\file_put_contents;
use function Safe\json_encode;
use function Safe\preg_replace;
use const FILE_APPEND;
use const PHP_EOL;

final class TracyLogger extends Logger
{
	public static function formatLogLine(mixed $message, ?string $exceptionFile = null): string
	{
		return implode(' ', [
			preg_replace('#\s*\r?\n\s*#', ' ', self::formatMessage($message)),
			' @  ' . Helpers::getSource(),
			$exceptionFile ? ' @@  ' . basename($exceptionFile) : null,
		]) . ($message instanceof \Throwable ? PHP_EOL . strstr((string) $message, 'Stack trace:') : '');
	}

	public function log(mixed $message, string $level = self::INFO): ?string
	{
		/** @see \Tracy\Bridges\Psr\TracyToPsrLoggerAdapter::log() */
		if (is_array($message) && isset($message['exception']) && $message['exception'] instanceof \Throwable) {
			$context = $message['context'] ?? [];
			$message = $message['exception'];
		}

		$exceptionFile = parent::log($message, $level);

		set_error_handler(
			function ($severity, $message, $file, $line): void {
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
