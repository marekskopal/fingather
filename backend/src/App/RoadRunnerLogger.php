<?php

declare(strict_types=1);

namespace FinGather\App;

use ErrorException;
use Psr\Log\LoggerInterface;
use Tracy\Bridges\Psr\TracyToPsrLoggerAdapter;
use Tracy\Debugger;
use Tracy\Helpers;
use Tracy\Logger;

class RoadRunnerLogger
{
	public static function initLogger(?string $directory): LoggerInterface
	{
		// @see https://github.com/nette/tracy/issues/280
		Debugger::setLogger(new class ($directory) extends Logger {
			public static function formatLogLine($message, ?string $exceptionFile = null): string
			{
				// Legito: remove date() from log line and append Stack trace
				return implode(' ', [
						\Safe\preg_replace('#\s*\r?\n\s*#', ' ', static::formatMessage($message)),
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
					function ($severity, $message, $file, $line) {
						throw new ErrorException($message, $severity, $severity, $file, $line);
					},
				);

				try {
					$log = static::formatLogLine($message, $exceptionFile);
					$log = str_replace(' @@ ', \Safe\json_encode($context ?? []) . ' @@ ', $log);
					\Safe\file_put_contents('php://stderr', $log, FILE_APPEND);
				} finally {
					restore_error_handler();
				}

				return $exceptionFile;
			}
		});

		\Safe\mb_internal_encoding('UTF-8');
		error_reporting(E_ALL);
		\Safe\ini_set('display_errors', 'stderr'); // RoadRunner logger collects stderr output
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'roadrunner/sapi'; // Force Tracy to send error response in php-cli mode
		Debugger::$strictMode = true; // Convert E_WARNING to \ErrorException
		Debugger::enable(true, $directory);

		return new TracyToPsrLoggerAdapter(Debugger::getLogger());
	}
}
