<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\ApiImport;
use FinGather\Model\Entity\Enum\ApiImportStatusEnum;

/** @extends ARepository<ApiImport> */
final class ApiImportRepository extends ARepository
{
	/** @return iterable<ApiImport> */
	public function findApiImports(?int $userId = null, ?int $portfolioId = null, ?ApiImportStatusEnum $apiImportStatus = null): iterable
	{
		$apiImportsSelect = $this->select();

		if ($userId !== null) {
			$apiImportsSelect->where('user_id', $userId);
		}

		if ($portfolioId !== null) {
			$apiImportsSelect->where('portfolio_id', $portfolioId);
		}

		if ($apiImportStatus !== null) {
			$apiImportsSelect->where('status', $apiImportStatus);
		}

		return $apiImportsSelect->fetchAll();
	}

	public function findApiImport(int $apiImportId): ?ApiImport
	{
		return $this->select()->where('id', $apiImportId)->fetchOne();
	}

	public function findLastApiImport(int $apiKey): ?ApiImport
	{
		return $this->select()
			->where('api_key_id', $apiKey)
			->orderBy('date_to', 'DESC')
			->fetchOne();
	}
}