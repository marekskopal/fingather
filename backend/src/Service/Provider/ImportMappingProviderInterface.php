<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Dto\ImportStartDto;
use FinGather\Model\Entity\Broker;
use FinGather\Model\Entity\ImportMapping;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\User;

interface ImportMappingProviderInterface
{
	/** @return list<ImportMapping> */
	public function getPortfolioImportMappings(User $user, Portfolio $portfolio): array;

	public function getImportMapping(User $user, int $importMappingId): ?ImportMapping;

	public function updateImportMapping(ImportMapping $importMapping, Ticker $ticker): ImportMapping;

	public function deleteImportMapping(ImportMapping $importMapping): void;

	/** @return array<string, ImportMapping> */
	public function getImportMappings(User $user, Portfolio $portfolio, Broker $broker): array;

	public function createImportMappingFromImportStart(User $user, ImportStartDto $importStart): void;
}
