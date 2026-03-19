<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Dto\ApiImportProcessCheckDto;
use FinGather\Model\Entity\ApiImport;

interface ApiImportProcessCheckProviderInterface
{
	public function createApiImportProcessCheck(ApiImport $apiImport): ApiImportProcessCheckDto;
}
