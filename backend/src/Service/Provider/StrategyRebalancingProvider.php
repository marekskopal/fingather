<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Dto\StrategyRebalancingDto;
use FinGather\Dto\StrategyRebalancingItemDto;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Strategy;
use FinGather\Model\Entity\StrategyItem;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\AssetDataDto;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use FinGather\Utils\CalculatorUtils;
use RuntimeException;

class StrategyRebalancingProvider
{
	public function __construct(
		private readonly PortfolioDataProvider $portfolioDataProvider,
		private readonly AssetProvider $assetProvider,
		private readonly AssetDataProvider $assetDataProvider,
		private readonly GroupDataProvider $groupDataProvider,
		private readonly GroupProvider $groupProvider,
		private readonly CurrencyProvider $currencyProvider,
		private readonly ExchangeRateProvider $exchangeRateProvider,
	) {
	}

	public function getStrategyRebalancing(
		User $user,
		Portfolio $portfolio,
		Strategy $strategy,
		DateTimeImmutable $dateTime,
		Decimal $cashToInvest,
		?int $cashCurrencyId,
		bool $allowSelling,
	): StrategyRebalancingDto {
		$portfolioData = $this->portfolioDataProvider->getPortfolioData($user, $portfolio, $dateTime);
		$cashToInvest = $this->convertCashToPortfolioCurrency($cashToInvest, $cashCurrencyId, $portfolio, $dateTime);
		$totalPortfolioValue = $portfolioData->value->add($cashToInvest);

		$assetDataMap = $this->buildAssetDataMap($user, $portfolio, $dateTime);
		$groupDataMap = $this->buildGroupDataMap($user, $portfolio, $dateTime);

		$items = [];
		$totalTargetPercentage = 0.0;
		$totalActualPercentage = 0.0;
		$totalCurrentValue = new Decimal('0');

		foreach ($strategy->strategyItems as $item) {
			$totalTargetPercentage += $item->percentage->toFloat();

			if ($item->asset !== null) {
				$assetData = array_key_exists($item->asset->id, $assetDataMap) ? $assetDataMap[$item->asset->id] : null;
				$currentValue = $assetData->value ?? new Decimal('0');
				$totalCurrentValue = $totalCurrentValue->add($currentValue);
				$totalActualPercentage += CalculatorUtils::toPercentage($currentValue, $portfolioData->value);
				$items[] = $this->buildAssetItem($item, $assetData, $portfolioData->value, $totalPortfolioValue, $allowSelling);
			} elseif ($item->group !== null) {
				$groupData = array_key_exists($item->group->id, $groupDataMap) ? $groupDataMap[$item->group->id] : null;
				$currentValue = $groupData->value ?? new Decimal('0');
				$totalCurrentValue = $totalCurrentValue->add($currentValue);
				$totalActualPercentage += CalculatorUtils::toPercentage($currentValue, $portfolioData->value);
				$items[] = $this->buildGroupItem($item, $groupData, $portfolioData->value, $totalPortfolioValue, $allowSelling);
			}
		}

		$othersTargetPercentage = round(100.0 - $totalTargetPercentage, 2);
		$othersActualPercentage = round(100.0 - $totalActualPercentage, 2);

		if ($othersTargetPercentage > 0.0 || $othersActualPercentage > 0.0) {
			$items[] = $this->buildOthersItem(
				$portfolioData->value->sub($totalCurrentValue),
				$totalPortfolioValue,
				$othersTargetPercentage,
				$othersActualPercentage,
			);
		}

		return new StrategyRebalancingDto(
			id: $strategy->id,
			name: $strategy->name,
			portfolioValue: $portfolioData->value,
			cashToInvest: $cashToInvest,
			items: $items,
		);
	}

	private function convertCashToPortfolioCurrency(
		Decimal $cashToInvest,
		?int $cashCurrencyId,
		Portfolio $portfolio,
		DateTimeImmutable $dateTime,
	): Decimal {
		if ($cashCurrencyId === null || $cashCurrencyId === $portfolio->currency->id) {
			return $cashToInvest;
		}

		$cashCurrency = $this->currencyProvider->getCurrency($cashCurrencyId);
		if ($cashCurrency === null) {
			throw new RuntimeException('Cash currency with id "' . $cashCurrencyId . '" was not found.');
		}

		$exchangeRate = $this->exchangeRateProvider->getExchangeRate($dateTime, $cashCurrency, $portfolio->currency);

		return $cashToInvest->mul($exchangeRate);
	}

	/** @return array<int, AssetDataDto> */
	private function buildAssetDataMap(User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): array
	{
		$map = [];
		$assets = $this->assetProvider->getAssets(user: $user, portfolio: $portfolio, dateTime: $dateTime);
		foreach ($assets as $asset) {
			$assetData = $this->assetDataProvider->getAssetData($user, $portfolio, $asset, $dateTime);
			if ($assetData === null || $assetData->isClosed()) {
				continue;
			}
			$map[$asset->id] = $assetData;
		}
		return $map;
	}

	/** @return array<int, CalculatedDataDto> */
	private function buildGroupDataMap(User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): array
	{
		$map = [];
		$groups = iterator_to_array($this->groupProvider->getGroups($user, $portfolio), false);
		foreach ($groups as $group) {
			$map[$group->id] = $this->groupDataProvider->getGroupData($group, $user, $portfolio, $dateTime);
		}
		return $map;
	}

	private function buildAssetItem(
		StrategyItem $item,
		?AssetDataDto $assetData,
		Decimal $portfolioValue,
		Decimal $totalPortfolioValue,
		bool $allowSelling,
	): StrategyRebalancingItemDto {
		assert($item->asset !== null);

		$currentValue = $assetData->value ?? new Decimal('0');
		$currentPrice = $assetData->price ?? new Decimal('0');
		$targetPercentage = $item->percentage->toFloat();
		$actualPercentage = CalculatorUtils::toPercentage($currentValue, $portfolioValue);

		$targetValue = $totalPortfolioValue->mul($item->percentage)->div(new Decimal('100'));
		$rawTrade = $targetValue->sub($currentValue);
		$suggestedTradeValue = $allowSelling || $rawTrade->isPositive() ? $rawTrade : new Decimal('0');
		$suggestedTradeUnits = !$currentPrice->isZero() ? $suggestedTradeValue->div($currentPrice) : null;

		return new StrategyRebalancingItemDto(
			name: $item->asset->ticker->name,
			color: null,
			assetId: $item->asset->id,
			groupId: null,
			isOthers: false,
			targetPercentage: $targetPercentage,
			actualPercentage: $actualPercentage,
			differencePercentage: round($actualPercentage - $targetPercentage, 2),
			currentValue: $currentValue,
			targetValue: $targetValue,
			suggestedTradeValue: $suggestedTradeValue,
			suggestedTradeUnits: $suggestedTradeUnits,
			currentPrice: $currentPrice,
		);
	}

	private function buildGroupItem(
		StrategyItem $item,
		?CalculatedDataDto $groupData,
		Decimal $portfolioValue,
		Decimal $totalPortfolioValue,
		bool $allowSelling,
	): StrategyRebalancingItemDto {
		assert($item->group !== null);

		$currentValue = $groupData->value ?? new Decimal('0');
		$targetPercentage = $item->percentage->toFloat();
		$actualPercentage = CalculatorUtils::toPercentage($currentValue, $portfolioValue);

		$targetValue = $totalPortfolioValue->mul($item->percentage)->div(new Decimal('100'));
		$rawTrade = $targetValue->sub($currentValue);
		$suggestedTradeValue = $allowSelling || $rawTrade->isPositive() ? $rawTrade : new Decimal('0');

		return new StrategyRebalancingItemDto(
			name: $item->group->name,
			color: $item->group->color,
			assetId: null,
			groupId: $item->group->id,
			isOthers: false,
			targetPercentage: $targetPercentage,
			actualPercentage: $actualPercentage,
			differencePercentage: round($actualPercentage - $targetPercentage, 2),
			currentValue: $currentValue,
			targetValue: $targetValue,
			suggestedTradeValue: $suggestedTradeValue,
			suggestedTradeUnits: null,
			currentPrice: null,
		);
	}

	private function buildOthersItem(
		Decimal $othersCurrentValue,
		Decimal $totalPortfolioValue,
		float $othersTargetPercentage,
		float $othersActualPercentage,
	): StrategyRebalancingItemDto {
		$targetValue = $totalPortfolioValue->mul(new Decimal((string) $othersTargetPercentage))->div(new Decimal('100'));

		return new StrategyRebalancingItemDto(
			name: 'Others',
			color: null,
			assetId: null,
			groupId: null,
			isOthers: true,
			targetPercentage: $othersTargetPercentage,
			actualPercentage: $othersActualPercentage,
			differencePercentage: round($othersActualPercentage - $othersTargetPercentage, 2),
			currentValue: $othersCurrentValue,
			targetValue: $targetValue,
			suggestedTradeValue: new Decimal('0'),
			suggestedTradeUnits: null,
			currentPrice: null,
		);
	}
}
