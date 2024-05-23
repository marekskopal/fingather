<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use Cycle\Database\Exception\StatementException\ConstrainException;
use DateTimeImmutable;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\AssetData;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\AssetDataRepository;
use FinGather\Service\DataCalculator\AssetDataCalculator;
use FinGather\Utils\DateTimeUtils;

class AssetDataProvider
{
	public function __construct(
		private readonly AssetDataRepository $assetDataRepository,
		private readonly AssetDataCalculator $assetDataCalculator,
	) {
	}

	public function getAssetData(User $user, Portfolio $portfolio, Asset $asset, DateTimeImmutable $dateTime): ?AssetData
	{
		$dateTime = DateTimeUtils::setEndOfDateTime($dateTime);

		$assetData = $this->assetDataRepository->findAssetData(
			userId: $user->getId(),
			portfolioId: $portfolio->getId(),
			assetId: $asset->getId(),
			date: $dateTime,
		);

		if ($assetData !== null) {
			return $assetData;
		}

		$assetDataDto = $this->assetDataCalculator->calculate($user, $portfolio, $asset, $dateTime);
		if ($assetDataDto === null) {
			return null;
		}

		$assetData = new AssetData(
			user: $user,
			portfolio: $portfolio,
			asset: $asset,
			date: $dateTime,
			price: $assetDataDto->price,
			units: $assetDataDto->units,
			value: $assetDataDto->value,
			transactionValue: $assetDataDto->transactionValue,
			transactionValueDefaultCurrency: $assetDataDto->transactionValueDefaultCurrency,
			averagePrice: $assetDataDto->averagePrice,
			averagePriceDefaultCurrency: $assetDataDto->averagePriceDefaultCurrency,
			gain: $assetDataDto->gain,
			gainDefaultCurrency: $assetDataDto->gainDefaultCurrency,
			gainPercentage: $assetDataDto->gainPercentage,
			gainPercentagePerAnnum: $assetDataDto->gainPercentagePerAnnum,
			realizedGain: $assetDataDto->realizedGain,
			realizedGainDefaultCurrency: $assetDataDto->realizedGainDefaultCurrency,
			dividendGain: $assetDataDto->dividendGain,
			dividendGainDefaultCurrency: $assetDataDto->dividendGainDefaultCurrency,
			dividendGainPercentage: $assetDataDto->dividendGainPercentage,
			dividendGainPercentagePerAnnum: $assetDataDto->dividendGainPercentagePerAnnum,
			fxImpact: $assetDataDto->fxImpact,
			fxImpactPercentage: $assetDataDto->fxImpactPercentage,
			fxImpactPercentagePerAnnum: $assetDataDto->fxImpactPercentagePerAnnum,
			return: $assetDataDto->return,
			returnPercentage: $assetDataDto->returnPercentage,
			returnPercentagePerAnnum: $assetDataDto->returnPercentagePerAnnum,
			tax: $assetDataDto->tax,
			taxDefaultCurrency: $assetDataDto->taxDefaultCurrency,
			fee: $assetDataDto->fee,
			feeDefaultCurrency: $assetDataDto->feeDefaultCurrency,
			firstTransactionActionCreated: $assetDataDto->firstTransactionActionCreated,
		);

		try {
			$this->assetDataRepository->persist($assetData);
		} catch (ConstrainException) {
			$assetData = $this->assetDataRepository->findAssetData(
				userId: $user->getId(),
				portfolioId: $portfolio->getId(),
				assetId: $asset->getId(),
				date: $dateTime,
			);
			assert($assetData instanceof AssetData);
		}

		return $assetData;
	}

	public function deleteAssetData(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $date = null): void
	{
		$this->assetDataRepository->deleteAssetData($user?->getId(), $portfolio?->getId(), $date);
	}
}
