<?php

declare(strict_types=1);

namespace FinGather\Service\Queue;

use FinGather\Service\Queue\Enum\QueueEnum;
use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner\Environment;
use Spiral\RoadRunner\Jobs\Jobs;
use Spiral\RoadRunner\Jobs\Options;
use Spiral\RoadRunner\Jobs\Task\QueuedTaskInterface;

final class QueuePublisher
{
	private readonly Jobs $jobs;

	private const int DefaultDelay = 5;

	public function __construct()
	{
		/** @var non-empty-string $address */
		$address = Environment::fromGlobals()->getRPCAddress();
		$this->jobs = new Jobs(
			RPC::create($address),
		);
	}

	/** @param positive-int $delay */
	public function publishMessage(object $message, QueueEnum $queueType, int $delay = self::DefaultDelay): QueuedTaskInterface
	{
		$queue = $this->jobs->connect($queueType->value);

		$payload = json_encode($message);
		if ($payload === false) {
			throw new \RuntimeException('Failed to encode message to JSON.');
		}

		return $queue->dispatch($queue->create($queueType->value, $payload, new Options(
			delay: $delay,
		)));
	}
}
