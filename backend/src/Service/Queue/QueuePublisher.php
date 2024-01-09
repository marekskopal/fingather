<?php

declare(strict_types=1);

namespace FinGather\Service\Queue;

use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner\Jobs\Jobs;
use Spiral\RoadRunner\Jobs\Options;
use Spiral\RoadRunner\Jobs\Task\QueuedTaskInterface;
use function Safe\json_encode;

class QueuePublisher
{
	private readonly Jobs $jobs;

	public function __construct()
	{
		$this->jobs = new Jobs(
			RPC::create('tcp://127.0.0.1:6001'),
		);
	}

	/** @param non-empty-string $queueName */
	public function publishMessage(object $message, string $queueName): QueuedTaskInterface
	{
		$queue = $this->jobs->connect($queueName);

		$payload = json_encode($message);

		return $queue->dispatch($queue->create($queueName, $payload, new Options(
			delay: 5,
		)));
	}
}
