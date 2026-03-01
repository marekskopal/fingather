<?php

declare(strict_types=1);

namespace FinGather\Jobs\Handler;

use FinGather\Dto\ApiImportPrepareCheckDto;
use FinGather\Jobs\Message\ReceivedMessageInterface;
use FinGather\Service\Import\ApiImport\ApiImportService;
use FinGather\Service\Task\TaskServiceInterface;

final class ApiImportPrepareCheckHandler implements JobHandler
{
	public function __construct(private readonly ApiImportService $apiImportService, private readonly TaskServiceInterface $taskService)
	{
	}

	public function handle(ReceivedMessageInterface $message): void
	{
		$apiImportPrepareCheck = $this->taskService->getPayloadDto($message, ApiImportPrepareCheckDto::class);

		$this->apiImportService->prepareImport($apiImportPrepareCheck);
	}
}
