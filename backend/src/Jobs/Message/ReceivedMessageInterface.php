<?php

declare(strict_types=1);

namespace FinGather\Jobs\Message;

interface ReceivedMessageInterface
{
	public function getPayload(): string;

	public function getQueue(): string;
}
