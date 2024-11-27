<?php

declare(strict_types=1);

namespace FinGather\Jobs\Handler;

use FinGather\Dto\ApiImportProcessCheckDto;
use FinGather\Service\Import\ApiImport\ApiImportService;
use FinGather\Service\Task\TaskServiceInterface;
use Spiral\RoadRunner\Jobs\Task\ReceivedTaskInterface;

final class ApiImportProcessCheckHandler implements JobHandler
{
	public function __construct(private readonly ApiImportService $apiImportService, private readonly TaskServiceInterface $taskService)
	{
	}

	public function handle(ReceivedTaskInterface $task): void
	{
		$apiImportProcessCheck = $this->taskService->getPayloadDto($task, ApiImportProcessCheckDto::class);

		$this->apiImportService->processImport($apiImportProcessCheck);
	}
}
