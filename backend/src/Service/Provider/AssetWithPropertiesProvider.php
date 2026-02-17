<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Dto\AssetDto;
use FinGather\Dto\AssetsWithPropertiesDto;
use FinGather\Dto\AssetWithPropertiesDto;
use FinGather\Dto\Enum\AssetOrderEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Utils\CalculatorUtils;

class AssetWithPropertiesProvider
{
	public function __construct(
		private readonly AssetProvider $assetProvider,
		private readonly AssetDataProvider $assetDataProvider,
		private readonly TickerDataProvider $tickerDataProvider,
		private readonly PortfolioDataProvider $portfolioDataProvider,
	) {
	}

	public function getAssetsWithAssetData(
		User $user,
		Portfolio $portfolio,
		DateTimeImmutable $dateTime,
		AssetOrderEnum $orderBy,
	): AssetsWithPropertiesDto {
		$portfolioData = $this->portfolioDataProvider->getPortfolioData($user, $portfolio, $dateTime);
		$assets = $this->assetProvider->getAssets(user: $user, portfolio: $portfolio);

		$openAssets = [];
		$closedAssets = [];
		$watchedAssets = [];

		foreach ($assets as $asset) {
			$assetData = $this->assetDataProvider->getAssetData($user, $portfolio, $asset, $dateTime);
			if ($assetData === null) {
				$lastTickerDataClose = $this->tickerDataProvider->getLastTickerDataClose($asset->ticker, $dateTime);
				assert($lastTickerDataClose !== null);
				$watchedAssets[] = AssetDto::fromEntity($asset, $lastTickerDataClose);

				continue;
			}

			$assetDto = AssetWithPropertiesDto::fromEntity(
				$asset,
				$assetData,
				CalculatorUtils::toPercentage($assetData->value, $portfolioData->value),
			);

			if ($assetData->isClosed()) {
				$closedAssets[] = $assetDto;
			} else {
				$openAssets[] = $assetDto;
			}
		}

		match ($orderBy) {
			AssetOrderEnum::TickerName => usort(
				$openAssets,
				fn (AssetWithPropertiesDto $a, AssetWithPropertiesDto $b) => $a->ticker->ticker <=> $b->ticker->ticker,
			),
			AssetOrderEnum::Price => usort(
				$openAssets,
				fn (AssetWithPropertiesDto $a, AssetWithPropertiesDto $b) => $b->price <=> $a->price,
			),
			AssetOrderEnum::Units => usort(
				$openAssets,
				fn (AssetWithPropertiesDto $a, AssetWithPropertiesDto $b) => $b->units <=> $a->units,
			),
			AssetOrderEnum::Value => usort(
				$openAssets,
				fn (AssetWithPropertiesDto $a, AssetWithPropertiesDto $b) => $b->value <=> $a->value,
			),
			AssetOrderEnum::Gain => usort(
				$openAssets,
				fn (AssetWithPropertiesDto $a, AssetWithPropertiesDto $b) => $b->gainDefaultCurrency <=> $a->gainDefaultCurrency,
			),
			AssetOrderEnum::DividendYield => usort(
				$openAssets,
				fn (AssetWithPropertiesDto $a, AssetWithPropertiesDto $b) => $b->dividendYieldDefaultCurrency <=> $a->dividendYieldDefaultCurrency,
			),
			AssetOrderEnum::FxImpact => usort(
				$openAssets,
				fn (AssetWithPropertiesDto $a, AssetWithPropertiesDto $b) => $b->fxImpact <=> $a->fxImpact,
			),
			AssetOrderEnum::Return => usort(
				$openAssets,
				fn (AssetWithPropertiesDto $a, AssetWithPropertiesDto $b) => $b->return <=> $a->return,
			),
		};

		return new AssetsWithPropertiesDto(openAssets: $openAssets, closedAssets: $closedAssets, watchedAssets: $watchedAssets);
	}
}
