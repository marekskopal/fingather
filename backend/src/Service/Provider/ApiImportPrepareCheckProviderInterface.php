<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Dto\ApiImportPrepareCheckDto;
use FinGather\Model\Entity\ApiKey;

interface ApiImportPrepareCheckProviderInterface
{
	public function createApiImportPrepareCheck(ApiKey $apiKey): ApiImportPrepareCheckDto;
}
