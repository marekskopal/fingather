<?php

declare(strict_types=1);

namespace FinGather\Jobs\Handler;

use FinGather\Dto\ApiImportProcessCheckDto;
use FinGather\Jobs\Message\ReceivedMessageInterface;
use FinGather\Service\Import\ApiImport\ApiImportService;
use FinGather\Service\Task\TaskServiceInterface;

final class ApiImportProcessCheckHandler implements JobHandler
{
	public function __construct(private readonly ApiImportService $apiImportService, private readonly TaskServiceInterface $taskService)
	{
	}

	public function handle(ReceivedMessageInterface $message): void
	{
		$apiImportProcessCheck = $this->taskService->getPayloadDto($message, ApiImportProcessCheckDto::class);

		$this->apiImportService->processImport($apiImportProcessCheck);
	}
}
