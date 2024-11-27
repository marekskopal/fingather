<?php

declare(strict_types=1);

namespace FinGather\Service\Task;

use FinGather\Dto\ArrayFactoryInterface;
use Nette\Utils\Json;
use Spiral\RoadRunner\Jobs\Task\ReceivedTaskInterface;

class TaskService implements TaskServiceInterface
{
	/**
	 * @param class-string<T> $dtoClass
	 * @return T
	 * @template T of ArrayFactoryInterface
	 */
	public function getPayloadDto(ReceivedTaskInterface $task, string $dtoClass): object
	{
		/** @var array<mixed> $decodedPayload */
		$decodedPayload = Json::decode($task->getPayload(), forceArrays: true);

		return $dtoClass::fromArray($decodedPayload);
	}
}
