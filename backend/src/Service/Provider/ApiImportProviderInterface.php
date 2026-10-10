<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\ApiImport;
use FinGather\Model\Entity\ApiKey;
use FinGather\Model\Entity\Enum\ApiImportStatusEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;

interface ApiImportProviderInterface
{
	/** @return list<ApiImport> */
	public function getApiImports(?User $user = null, ?Portfolio $portfolio = null, ?ApiImportStatusEnum $apiImportStatus = null): array;

	public function getApiImport(int $apiImportId): ?ApiImport;

	public function getLastApiImport(ApiKey $apiKey): ?ApiImport;

	public function createApiImport(
		User $user,
		Portfolio $portfolio,
		ApiKey $apiKey,
		DateTimeImmutable $dateFrom,
		DateTimeImmutable $dateTo,
		?int $reportId,
	): ApiImport;

	public function updateApiImport(ApiImport $apiImport, ApiImportStatusEnum $status, ?string $error = null): ApiImport;
}
