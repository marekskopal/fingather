<?php

declare(strict_types=1);

namespace FinGather\Service\Task;

use FinGather\Dto\ArrayFactoryInterface;
use FinGather\Jobs\Message\ReceivedMessageInterface;
use Nette\Utils\Json;

class TaskService implements TaskServiceInterface
{
	/**
	 * @param class-string<T> $dtoClass
	 * @return T
	 * @template T of ArrayFactoryInterface
	 */
	public function getPayloadDto(ReceivedMessageInterface $message, string $dtoClass): object
	{
		/** @var array<mixed> $decodedPayload */
		$decodedPayload = Json::decode($message->getPayload(), forceArrays: true);

		return $dtoClass::fromArray($decodedPayload);
	}
}
