<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Sector;
use FinGather\Model\Entity\User;

class SectorProvider
{
	public function __construct(private readonly AssetProvider $assetProvider,)
	{
	}

	/** @return array<int, Sector> */
	public function getSectorsFromAssets(User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): array
	{
		$sectors = [];

		$assets = $this->assetProvider->getAssets(user: $user, portfolio: $portfolio, dateTime: $dateTime);
		foreach ($assets as $asset) {
			$sector = $asset->ticker->sector;

			if (array_key_exists($sector->id, $sectors)) {
				continue;
			}

			$sectors[$sector->id] = $sector;
		}

		return $sectors;
	}
}
