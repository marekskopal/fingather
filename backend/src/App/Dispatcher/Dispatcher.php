<?php

declare(strict_types=1);

namespace FinGather\App\Dispatcher;

use Spiral\RoadRunner\EnvironmentInterface;

interface Dispatcher
{
	public function canServe(EnvironmentInterface $env): bool;

	public function serve(): void;
}
