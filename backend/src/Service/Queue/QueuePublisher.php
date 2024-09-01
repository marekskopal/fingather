<?php

declare(strict_types=1);

namespace FinGather\Service\Queue;

use FinGather\Service\Queue\Enum\QueueEnum;
use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner\Environment;
use Spiral\RoadRunner\Jobs\Jobs;
use Spiral\RoadRunner\Jobs\Options;
use Spiral\RoadRunner\Jobs\Task\QueuedTaskInterface;
use function Safe\json_encode;

final class QueuePublisher
{
	private readonly Jobs $jobs;

	public function __construct()
	{
		/** @var non-empty-string $address */
		$address = Environment::fromGlobals()->getRPCAddress();
		$this->jobs = new Jobs(
			RPC::create($address),
		);
	}

	public function publishMessage(object $message, QueueEnum $queueType): QueuedTaskInterface
	{
		$queue = $this->jobs->connect($queueType->value);

		$payload = json_encode($message);

		return $queue->dispatch($queue->create($queueType->value, $payload, new Options(
			delay: 5,
		)));
	}
}
