<?php

declare(strict_types=1);

namespace FinGather\Jobs\Handler;

use Spiral\RoadRunner\Jobs\Task\ReceivedTaskInterface;

interface JobHandler
{
	public function handle(ReceivedTaskInterface $task): void;
}
