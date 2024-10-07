<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Country;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Industry;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Sector;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\DataCalculator;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use FinGather\Utils\DateTimeUtils;

class CalculatedGroupDataProvider
{
	public function __construct(
		private readonly DataCalculator $dataCalculator,
		private readonly AssetProvider $assetProvider,
		private readonly AssetDataProvider $assetDataProvider,
	) {
	}

	public function getCalculatedData(
		User $user,
		Portfolio $portfolio,
		DateTimeImmutable $dateTime,
		?Group $group = null,
		?Country $country = null,
		?Sector $sector = null,
		?Industry $industry = null,
	): CalculatedDataDto
	{
		$dateTime = DateTimeUtils::setEndOfDateTime($dateTime);

		$assetDatas = [];
		$firstTransactionActionCreated = new \Safe\DateTimeImmutable();

		$assets = $this->assetProvider->getAssets(
			user: $user,
			portfolio: $portfolio,
			dateTime: $dateTime,
			group: $group,
			country: $country,
			sector: $sector,
			industry: $industry,
		);
		foreach ($assets as $asset) {
			$assetData = $this->assetDataProvider->getAssetData($user, $portfolio, $asset, $dateTime);
			if ($assetData === null) {
				continue;
			}

			if ($firstTransactionActionCreated > $assetData->firstTransactionActionCreated) {
				$firstTransactionActionCreated = $assetData->firstTransactionActionCreated;
			}

			$assetDatas[] = $assetData;
		}

		return $this->dataCalculator->calculate($assetDatas, $dateTime, $firstTransactionActionCreated);
	}
}
