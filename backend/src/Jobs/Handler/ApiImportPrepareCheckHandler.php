<?php

declare(strict_types=1);

namespace FinGather\Jobs\Handler;

use FinGather\Dto\ApiImportPrepareCheckDto;
use FinGather\Service\Import\ApiImport\ApiImportService;
use Spiral\RoadRunner\Jobs\Task\ReceivedTaskInterface;

final class ApiImportPrepareCheckHandler implements JobHandler
{
	public function __construct(private readonly ApiImportService $apiImportService)
	{
	}

	public function handle(ReceivedTaskInterface $task): void
	{
		/**
		 * @var array{
		 *     userId: int,
		 *     portfolioId: int,
		 *     apiKeyId: int,
		 * } $payload
		 */
		$payload = json_decode($task->getPayload(), associative: true);

		$apiImportPrepareCheck = ApiImportPrepareCheckDto::fromArray($payload);

		$this->apiImportService->prepareImport($apiImportPrepareCheck);
	}
}
