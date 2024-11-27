<?php

declare(strict_types=1);

namespace FinGather\Service\Task;

use FinGather\Dto\ArrayFactoryInterface;
use Spiral\RoadRunner\Jobs\Task\ReceivedTaskInterface;

interface TaskServiceInterface
{
	/**
	 * @param class-string<T> $dtoClass
	 * @return T
	 * @template T of ArrayFactoryInterface
	 */
	public function getPayloadDto(ReceivedTaskInterface $task, string $dtoClass): object;
}
