<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\ApiImport;
use FinGather\Model\Entity\ApiKey;
use FinGather\Model\Entity\Enum\ApiImportStatusEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\ApiImportRepository;

class ApiImportProvider
{
	public function __construct(private readonly ApiImportRepository $apiImportRepository)
	{
	}

	/** @return iterable<ApiImport> */
	public function getApiImports(?User $user = null, ?Portfolio $portfolio = null, ?ApiImportStatusEnum $apiImportStatus = null): iterable
	{
		return $this->apiImportRepository->findApiImports(
			userId: $user?->getId(),
			portfolioId: $portfolio?->getId(),
			apiImportStatus: $apiImportStatus,
		);
	}

	public function getApiImport(int $apiImportId): ?ApiImport
	{
		return $this->apiImportRepository->findApiImport($apiImportId);
	}

	public function getLastApiImport(ApiKey $apiKey): ?ApiImport
	{
		return $this->apiImportRepository->findLastApiImport($apiKey->getId());
	}

	public function createApiImport(
		User $user,
		Portfolio $portfolio,
		ApiKey $apiKey,
		DateTimeImmutable $dateFrom,
		DateTimeImmutable $dateTo,
		?int $reportId,
	): ApiImport {
		$apiImport = new ApiImport(
			user: $user,
			portfolio: $portfolio,
			apiKey: $apiKey,
			status: ApiImportStatusEnum::New,
			dateFrom: $dateFrom,
			dateTo: $dateTo,
			reportId: $reportId,
		);

		$this->apiImportRepository->persist($apiImport);

		return $apiImport;
	}

	public function updateApiImport(ApiImport $apiImport, ApiImportStatusEnum $status): ApiImport
	{
		$apiImport->setStatus($status);

		$this->apiImportRepository->persist($apiImport);

		return $apiImport;
	}
}
