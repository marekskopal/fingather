<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\AssetDataDto;

interface AssetDataProviderInterface
{
	public function getAssetData(User $user, Portfolio $portfolio, Asset $asset, DateTimeImmutable $dateTime): ?AssetDataDto;

	public function deleteAssetData(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $date = null): void;
}
