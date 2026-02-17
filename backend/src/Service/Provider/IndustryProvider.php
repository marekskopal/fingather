<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Industry;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;

final readonly class IndustryProvider
{
	public function __construct(private AssetProviderInterface $assetProvider,)
	{
	}

	/** @return array<int, Industry> */
	public function getIndustriesFromAssets(User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): array
	{
		$industries = [];

		$assets = $this->assetProvider->getAssets(user: $user, portfolio: $portfolio, dateTime: $dateTime);
		foreach ($assets as $asset) {
			$industry = $asset->ticker->industry;

			if (array_key_exists($industry->id, $industries)) {
				continue;
			}

			$industries[$industry->id] = $industry;
		}

		return $industries;
	}
}
