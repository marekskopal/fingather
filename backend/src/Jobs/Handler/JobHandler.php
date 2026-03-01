<?php

declare(strict_types=1);

namespace FinGather\Jobs\Handler;

use FinGather\Jobs\Message\ReceivedMessageInterface;

interface JobHandler
{
	public function handle(ReceivedMessageInterface $message): void;
}
