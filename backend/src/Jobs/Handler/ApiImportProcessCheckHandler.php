<?php

declare(strict_types=1);

namespace FinGather\Jobs\Handler;

use FinGather\Dto\ApiImportProcessCheckDto;
use FinGather\Service\Import\ApiImport\ApiImportService;
use Spiral\RoadRunner\Jobs\Task\ReceivedTaskInterface;
use function Safe\json_decode;

final class ApiImportProcessCheckHandler implements JobHandler
{
	public function __construct(private readonly ApiImportService $apiImportService,)
	{
	}

	public function handle(ReceivedTaskInterface $task): void
	{
		/**
		 * @var array{
		 *     apiImportId: int,
		 * } $payload
		 */
		$payload = json_decode($task->getPayload(), assoc: true);

		$apiImportProcessCheck = ApiImportProcessCheckDto::fromArray($payload);

		$this->apiImportService->processImport($apiImportProcessCheck);
	}
}
