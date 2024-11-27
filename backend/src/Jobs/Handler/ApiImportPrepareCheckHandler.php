<?php

declare(strict_types=1);

namespace FinGather\Jobs\Handler;

use FinGather\Dto\ApiImportPrepareCheckDto;
use FinGather\Service\Import\ApiImport\ApiImportService;
use FinGather\Service\Task\TaskServiceInterface;
use Spiral\RoadRunner\Jobs\Task\ReceivedTaskInterface;

final class ApiImportPrepareCheckHandler implements JobHandler
{
	public function __construct(private readonly ApiImportService $apiImportService, private readonly TaskServiceInterface $taskService)
	{
	}

	public function handle(ReceivedTaskInterface $task): void
	{
		$apiImportPrepareCheck = $this->taskService->getPayloadDto($task, ApiImportPrepareCheckDto::class);

		$this->apiImportService->prepareImport($apiImportPrepareCheck);
	}
}
