<?php

declare(strict_types=1);

namespace FinGather\Model\Repository;

use FinGather\Model\Entity\Dividend;
use Safe\DateTimeImmutable;

/** @extends ARepository<Dividend> */
class DividendRepository extends ARepository
{
	/** @return array<int, Dividend> */
	public function findDividends(int $userId, ?DateTimeImmutable $paidDateTo = null): array
	{
		$assetDividends = $this->select()
			->where('user_id', $userId);

		if ($paidDateTo !== null) {
			$assetDividends->where('paid_date', '<=', $paidDateTo);
		}

		return $assetDividends->fetchAll();
	}

	/** @return array<int, Dividend> */
	public function findAssetDividends(int $userId, int $assetId, ?DateTimeImmutable $paidDateTo = null): array
	{
		$assetDividends = $this->select()
			->where('user_id', $userId)
			->where('asset_id', $assetId);

		if ($paidDateTo !== null) {
			$assetDividends->where('paid_date', '<=', $paidDateTo);
		}

		return $assetDividends->fetchAll();
	}
}
