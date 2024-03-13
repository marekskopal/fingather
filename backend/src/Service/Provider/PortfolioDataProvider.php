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
		private readonly AssetDataProvider $assetDataProvider,
		private readonly TransactionProvider $transactionProvider,
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

		$assets = $this->assetProvider->getAssets(user: $user, portfolio: $portfolio, dateTime: $dateTime);
		foreach ($assets as $asset) {
			$assetData = $this->assetDataProvider->getAssetData($user, $portfolio, $asset, $dateTime);
			if ($assetData === null) {
				continue;
			}

			$assetDtos[] = AssetWithPropertiesDto::fromEntity($asset, $assetData);
		}

		$fistTransactionActionCreated = $this->transactionProvider->getFirstTransaction(
			$user,
			$portfolio,
		)?->getActionCreated() ?? $dateTime;

		$calculatedData = $this->dataCalculator->calculate($assetDtos, $dateTime, $fistTransactionActionCreated);

		$portfolioData = new PortfolioData(
			user: $user,
			portfolio: $portfolio,
			date: $dateTime,
			value: $calculatedData->value,
			transactionValue: $calculatedData->transactionValue,
			gain: $calculatedData->gain,
			gainPercentage: $calculatedData->gainPercentage,
			gainPercentagePerAnnum: $calculatedData->gainPercentagePerAnnum,
			dividendGain: $calculatedData->dividendGain,
			dividendGainPercentage: $calculatedData->dividendGainPercentage,
			dividendGainPercentagePerAnnum: $calculatedData->dividendGainPercentagePerAnnum,
			fxImpact: $calculatedData->fxImpact,
			fxImpactPercentage: $calculatedData->fxImpactPercentage,
			fxImpactPercentagePerAnnum: $calculatedData->fxImpactPercentagePerAnnum,
			return: $calculatedData->return,
			returnPercentage: $calculatedData->returnPercentage,
			returnPercentagePerAnnum: $calculatedData->returnPercentagePerAnnum,
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
