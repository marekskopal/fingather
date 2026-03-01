<?php

declare(strict_types=1);

namespace FinGather;

require_once __DIR__ . '/../vendor/autoload.php';

use FinGather\App\ApplicationFactory;
use FinGather\Response\ErrorResponse;
use FinGather\Service\Provider\CurrentTransactionProvider;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Log\LoggerInterface;

$application = ApplicationFactory::create();

$logger = $application->container->get(LoggerInterface::class);
assert($logger instanceof LoggerInterface);
$currentTransactionProvider = $application->container->get(CurrentTransactionProvider::class);
assert($currentTransactionProvider instanceof CurrentTransactionProvider);

$emitter = new SapiEmitter();

$handler = static function () use ($application, $logger, $emitter): void {
	try {
		$request = ServerRequestFactory::fromGlobals();
		$response = $application->handler->handle($request);
		$emitter->emit($response);
	} catch (\Throwable $e) {
		$logger->error($e);
		$emitter->emit(ErrorResponse::fromException($e));
	}
};

while (frankenphp_handle_request($handler)) {
	$application->dbContext->getOrm()->getEntityCache()->clear();
	$currentTransactionProvider->clear();
	gc_collect_cycles();
}
