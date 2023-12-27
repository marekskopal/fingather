<?php

declare(strict_types=1);

namespace FinGather\Service\Logger;

use Psr\Log\LoggerInterface;
use Tracy\Bridges\Psr\TracyToPsrLoggerAdapter;
use Tracy\Debugger;
use function Safe\ini_set;
use function Safe\mb_internal_encoding;
use const E_ALL;

final class Logger
{
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

		return new TracyToPsrLoggerAdapter(Debugger::getLogger());
	}
}
