<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Dto\AssetDto;
use FinGather\Model\Entity\PortfolioData;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\PortfolioDataRepository;
use FinGather\Service\DataCalculator\DataCalculator;
use Safe\DateTimeImmutable;

class PortfolioDataProvider
{
	public function __construct(
		private readonly PortfolioDataRepository $portfolioDataRepository,
		private readonly DataCalculator $dataCalculator,
		private readonly AssetProvider $assetProvider,
	) {
	}

	public function getPortfolioData(User $user, DateTimeImmutable $dateTime): PortfolioData
	{
		$dateTime = $dateTime->setTime(0, 0);

		$portfolioData = $this->portfolioDataRepository->findPortfolioData($user->getId(), $dateTime);
		if ($portfolioData !== null) {
			return $portfolioData;
		}

		$assetDtos = [];

		$assets = $this->assetProvider->getAssets($user, $dateTime);
		foreach ($assets as $asset) {
			$assetProperties = $this->assetProvider->getAssetProperties($user, $asset, $dateTime);
			if ($assetProperties === null) {
				continue;
			}

			$assetDtos[] = AssetDto::fromEntity($asset, $assetProperties);
		}

		$calculatedData = $this->dataCalculator->calculate($user, $dateTime, $assetDtos);

		$portfolioData = new PortfolioData(
			user: $user,
			date: $dateTime,
			value: (string) $calculatedData->value,
			transactionValue: (string) $calculatedData->transactionValue,
			gain: (string) $calculatedData->gain,
			gainPercentage: $calculatedData->gainPercentage,
			dividendGain: (string) $calculatedData->dividendGain,
			dividendGainPercentage: $calculatedData->dividendGainPercentage,
			fxImpact: (string) $calculatedData->fxImpact,
			fxImpactPercentage: $calculatedData->fxImpactPercentage,
			return: (string) $calculatedData->return,
			returnPercentage: $calculatedData->returnPercentage,
		);

		$this->portfolioDataRepository->persist($portfolioData);

		return $portfolioData;
	}

	public function deletePortfolioData(User $user, DateTimeImmutable $date): void
	{
		$this->portfolioDataRepository->deletePortfolioData($user->getId(), $date);
	}
}
