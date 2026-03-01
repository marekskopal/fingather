<?php

declare(strict_types=1);

namespace FinGather\Service\Queue;

use FinGather\Service\Queue\Enum\QueueEnum;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

final readonly class QueuePublisher
{
	private AMQPStreamConnection $connection;

	private AMQPChannel $channel;

	private const int DefaultDelay = 5;

	public function __construct()
	{
		$this->connection = new AMQPStreamConnection(
			(string) getenv('RABBITMQ_HOST'),
			(int) getenv('RABBITMQ_PORT'),
			(string) getenv('RABBITMQ_USER'),
			(string) getenv('RABBITMQ_PASSWORD'),
		);
		$this->channel = $this->connection->channel();
	}

	/** @param positive-int $delay */
	public function publishMessage(object $message, QueueEnum $queueType, int $delay = self::DefaultDelay): void
	{
		$payload = json_encode($message);
		if ($payload === false) {
			throw new \RuntimeException('Failed to encode message to JSON.');
		}

		sleep($delay);

		$this->channel->basic_publish(
			new AMQPMessage($payload, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]),
			'',
			$queueType->value,
		);
	}
}
