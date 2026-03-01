<?php

declare(strict_types=1);

namespace FinGather\Jobs\Message;

final readonly class AmqpReceivedMessage implements ReceivedMessageInterface
{
	public function __construct(private string $payload, private string $queue,)
	{
	}

	public function getPayload(): string
	{
		return $this->payload;
	}

	public function getQueue(): string
	{
		return $this->queue;
	}
}
