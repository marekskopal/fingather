<?php

declare(strict_types=1);

namespace FinGather\Jobs\Handler;

use FinGather\Dto\ApiImportProcessCheckDto;
use FinGather\Jobs\Message\ReceivedMessageInterface;
use FinGather\Service\Import\ApiImport\ApiImportService;
use FinGather\Service\Task\TaskServiceInterface;

final readonly class ApiImportProcessCheckHandler implements JobHandler
{
	public function __construct(private ApiImportService $apiImportService, private TaskServiceInterface $taskService)
	{
	}

	public function handle(ReceivedMessageInterface $message): void
	{
		$apiImportProcessCheck = $this->taskService->getPayloadDto($message, ApiImportProcessCheckDto::class);

		$this->apiImportService->processImport($apiImportProcessCheck);
	}
}
