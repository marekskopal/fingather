<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Dto\ApiImportPrepareCheckDto;
use FinGather\Model\Entity\ApiKey;
use FinGather\Service\Queue\Enum\QueueEnum;
use FinGather\Service\Queue\QueuePublisher;

final readonly class ApiImportPrepareCheckProvider
{
	public function __construct(private QueuePublisher $queuePublisher,)
	{
	}

	public function createApiImportPrepareCheck(ApiKey $apiKey): ApiImportPrepareCheckDto
	{
		$apiImportCheck = ApiImportPrepareCheckDto::fromApiKeyEntity($apiKey);

		$this->queuePublisher->publishMessage($apiImportCheck, QueueEnum::ApiImportPrepareCheck);

		return $apiImportCheck;
	}
}
