<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Dto\ApiImportProcessCheckDto;
use FinGather\Model\Entity\ApiImport;
use FinGather\Service\Queue\Enum\QueueEnum;
use FinGather\Service\Queue\QueuePublisher;

final readonly class ApiImportProcessCheckProvider
{
	public function __construct(private QueuePublisher $queuePublisher,)
	{
	}

	public function createApiImportProcessCheck(ApiImport $apiImport): ApiImportProcessCheckDto
	{
		$apiImportProcessCheck = ApiImportProcessCheckDto::fromEntity($apiImport);

		$this->queuePublisher->publishMessage($apiImportProcessCheck, QueueEnum::ApiImportProcessCheck);

		return $apiImportProcessCheck;
	}
}
