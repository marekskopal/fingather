<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use Cycle\Database\Exception\StatementException\ConstrainException;
use FinGather\Dto\AssetWithPropertiesDto;
use FinGather\Model\Entity\Portfolio;
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

	public function getPortfolioData(User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): PortfolioData
	{
		$dateTime = $dateTime->setTime(0, 0);

		$portfolioData = $this->portfolioDataRepository->findPortfolioData($user->getId(), $portfolio->getId(), $dateTime);
		if ($portfolioData !== null) {
			return $portfolioData;
		}

		$assetDtos = [];

		$assets = $this->assetProvider->getOpenAssets($user, $portfolio, $dateTime);
		foreach ($assets as $asset) {
			$assetProperties = $this->assetProvider->getAssetProperties($user, $portfolio, $asset, $dateTime);
			if ($assetProperties === null) {
				continue;
			}

			$assetDtos[] = AssetWithPropertiesDto::fromEntity($asset, $assetProperties);
		}

		$calculatedData = $this->dataCalculator->calculate($assetDtos);

		$portfolioData = new PortfolioData(
			user: $user,
			portfolio: $portfolio,
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

		try {
			$this->portfolioDataRepository->persist($portfolioData);
		} catch (ConstrainException) {
			$portfolioData = $this->portfolioDataRepository->findPortfolioData($user->getId(), $portfolio->getId(), $dateTime);
			assert($portfolioData instanceof PortfolioData);
		}

		return $portfolioData;
	}

	public function deletePortfolioData(User $user, Portfolio $portfolio, DateTimeImmutable $date): void
	{
		$this->portfolioDataRepository->deletePortfolioData($user->getId(), $portfolio->getId(), $date);
	}
}
