<?php

declare(strict_types=1);

namespace FinGather;

require_once __DIR__ . '/../vendor/autoload.php';

use FinGather\App\ApplicationFactory;
use FinGather\Jobs\Handler\ApiImportPrepareCheckHandler;
use FinGather\Jobs\Handler\ApiImportProcessCheckHandler;
use FinGather\Jobs\Handler\EmailVerifyHandler;
use FinGather\Jobs\Handler\JobHandler;
use FinGather\Jobs\Handler\UserWarmupHandler;
use FinGather\Jobs\Message\AmqpReceivedMessage;
use FinGather\Service\Provider\CurrentTransactionProvider;
use FinGather\Service\Queue\Enum\QueueEnum;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

$application = ApplicationFactory::create();

$logger = $application->container->get(LoggerInterface::class);
assert($logger instanceof LoggerInterface);

$currentTransactionProvider = $application->container->get(CurrentTransactionProvider::class);
assert($currentTransactionProvider instanceof CurrentTransactionProvider);

$connection = new AMQPStreamConnection(
	(string) getenv('RABBITMQ_HOST'),
	(int) getenv('RABBITMQ_PORT'),
	(string) getenv('RABBITMQ_USER'),
	(string) getenv('RABBITMQ_PASSWORD'),
);
$channel = $connection->channel();

$handlerMap = [
	QueueEnum::EmailVerify->value => EmailVerifyHandler::class,
	QueueEnum::ApiImportPrepareCheck->value => ApiImportPrepareCheckHandler::class,
	QueueEnum::ApiImportProcessCheck->value => ApiImportProcessCheckHandler::class,
	QueueEnum::UserWarmup->value => UserWarmupHandler::class,
];

$prefetch = (int) getenv('BACKEND_AMQP_CONSUMER_PREFETCH');

foreach (QueueEnum::cases() as $queue) {
	$channel->queue_declare($queue->value, false, true, false, false);
	$channel->basic_qos(0, $prefetch, false);
	$channel->basic_consume(
		$queue->value,
		'',
		false,
		false,
		false,
		false,
		static function (AMQPMessage $msg) use ($application, $logger, $currentTransactionProvider, $handlerMap): void {
			$queueName = $msg->getRoutingKey();

			if (is_string($queueName)) {
				try {
					$handlerClass = $handlerMap[$queueName]
						?? throw new \InvalidArgumentException('Unhandled queue [' . $queueName . ']');

					$logger->info('Handling task', ['queue' => $queueName]);

					/** @var JobHandler $handler */
					$handler = $application->container->get($handlerClass);
					$handler->handle(new AmqpReceivedMessage($msg->getBody(), $queueName));

					$msg->ack();
				} catch (\Throwable $e) {
					$logger->error($e);
					$msg->nack(false, true);
				}
			}

			$application->dbContext->getOrm()->getEntityCache()->clear();
			$currentTransactionProvider->clear();
			gc_collect_cycles();
		},
	);
}

$channel->consume();

$channel->close();
$connection->close();
