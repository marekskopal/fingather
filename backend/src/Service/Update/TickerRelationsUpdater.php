<?php

declare(strict_types=1);

namespace FinGather\Service\Update;

use FinGather\Model\Entity\Ticker;
use FinGather\Model\Repository\AssetRepository;

final class TickerRelationsUpdater
{
	public function __construct(
		private readonly TickerUpdater $tickerUpdater,
		private readonly SplitUpdater $splitUpdater,
		private readonly AssetRepository $assetRepository,
	) {
	}

	public function checkAndUpdateTicker(Ticker $ticker): void
	{
		if ($this->isTickerUsed($ticker)) {
			return;
		}

		$this->splitUpdater->updateSplits($ticker);
		$this->tickerUpdater->updateTicker($ticker);
	}

	private function isTickerUsed(Ticker $ticker): bool
	{
		return $this->assetRepository->findAssetByTickerId(
			tickerId: $ticker->id,
		) !== null;
	}
}
