<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Dto\AssetsWithPropertiesDto;
use FinGather\Dto\Enum\AssetOrderEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;

interface AssetWithPropertiesProviderInterface
{
	public function getAssetsWithAssetData(
		User $user,
		Portfolio $portfolio,
		DateTimeImmutable $dateTime,
		AssetOrderEnum $orderBy,
	): AssetsWithPropertiesDto;
}
