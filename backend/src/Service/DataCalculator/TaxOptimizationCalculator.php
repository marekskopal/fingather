<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\AssetDataDto;
use FinGather\Service\DataCalculator\Dto\TaxOptimizationDto;
use FinGather\Service\DataCalculator\Dto\TaxOptimizationRationaleEnum;
use FinGather\Service\DataCalculator\Dto\TaxOptimizationSuggestionDto;
use FinGather\Service\Provider\AssetDataProviderInterface;
use FinGather\Service\Provider\AssetProviderInterface;
use FinGather\Service\Provider\CurrentTransactionProviderInterface;
use FinGather\Service\Tax\Jurisdiction\TaxJurisdictionRulesFactory;
use FinGather\Service\Tax\Jurisdiction\TaxJurisdictionRulesInterface;
use const PHP_INT_MAX;

final readonly class TaxOptimizationCalculator
{
	private const int HoldForTaxFreeGainThresholdDays = 365;

	public function __construct(
		private AssetProviderInterface $assetProvider,
		private AssetDataProviderInterface $assetDataProvider,
		private CurrentTransactionProviderInterface $currentTransactionProvider,
		private TaxJurisdictionRulesFactory $jurisdictionFactory,
	) {
	}

	public function calculate(User $user, Portfolio $portfolio, ?DateTimeImmutable $asOf = null): TaxOptimizationDto
	{
		$asOf ??= new DateTimeImmutable();
		$rules = $this->jurisdictionFactory->forPortfolio($portfolio);
		$rate = $portfolio->estimatedTaxRate ?? $rules->defaultEstimatedTaxRate();
		$transactionsByAsset = $this->currentTransactionProvider->loadTransactions(user: $user, portfolio: $portfolio);

		$harvestNow = [];
		$holdForTaxFreeGain = [];
		$lossNoLongerDeductible = [];
		$alreadyTaxFree = [];
		$winningShortTerm = [];

		foreach ($this->assetProvider->getAssets(user: $user, portfolio: $portfolio, dateTime: $asOf) as $asset) {
			$assetData = $this->assetDataProvider->getAssetData(user: $user, portfolio: $portfolio, asset: $asset, dateTime: $asOf);
			if ($assetData === null || !$assetData->isOpen()) {
				continue;
			}

			$holdingVariesByBroker = $this->hasMultipleBuyBrokers($transactionsByAsset[$asset->id] ?? []);

			$this->categorize(
				$asset,
				$assetData,
				$asOf,
				$rules,
				$rate,
				$holdingVariesByBroker,
				$harvestNow,
				$holdForTaxFreeGain,
				$lossNoLongerDeductible,
				$alreadyTaxFree,
				$winningShortTerm,
			);
		}

		usort(
			$harvestNow,
			fn(TaxOptimizationSuggestionDto $a, TaxOptimizationSuggestionDto $b): int =>
				($a->daysUntilLongTerm ?? PHP_INT_MAX) <=> ($b->daysUntilLongTerm ?? PHP_INT_MAX),
		);
		usort(
			$holdForTaxFreeGain,
			fn(TaxOptimizationSuggestionDto $a, TaxOptimizationSuggestionDto $b): int =>
				($a->daysUntilLongTerm ?? PHP_INT_MAX) <=> ($b->daysUntilLongTerm ?? PHP_INT_MAX),
		);

		return new TaxOptimizationDto(
			asOfDate: $asOf->format('Y-m-d'),
			jurisdiction: $rules->jurisdiction(),
			longTermHoldingDays: $rules->longTermHoldingDays(),
			estimatedTaxRate: $rate,
			harvestNow: $harvestNow,
			holdForTaxFreeGain: $holdForTaxFreeGain,
			lossNoLongerDeductible: $lossNoLongerDeductible,
			alreadyTaxFree: $alreadyTaxFree,
			winningShortTerm: $winningShortTerm,
			estimatedTaxSavedByHarvestingNow: $this->sumTaxImpact($harvestNow),
			estimatedTaxSavedByWaiting: $this->sumTaxImpact($holdForTaxFreeGain),
		);
	}

	/**
	 * @param list<TaxOptimizationSuggestionDto> $harvestNow
	 * @param list<TaxOptimizationSuggestionDto> $holdForTaxFreeGain
	 * @param list<TaxOptimizationSuggestionDto> $lossNoLongerDeductible
	 * @param list<TaxOptimizationSuggestionDto> $alreadyTaxFree
	 * @param list<TaxOptimizationSuggestionDto> $winningShortTerm
	 */
	private function categorize(
		Asset $asset,
		AssetDataDto $assetData,
		DateTimeImmutable $asOf,
		TaxJurisdictionRulesInterface $rules,
		?Decimal $rate,
		bool $holdingVariesByBroker,
		array &$harvestNow,
		array &$holdForTaxFreeGain,
		array &$lossNoLongerDeductible,
		array &$alreadyTaxFree,
		array &$winningShortTerm,
	): void {
		$holdingDays = (int) $asOf->diff($assetData->firstTransactionActionCreated)->days;
		$gain = $assetData->gainDefaultCurrency;
		$longTermDays = $rules->longTermHoldingDays();
		$daysUntilLongTerm = $longTermDays !== null ? max(0, $longTermDays - $holdingDays) : null;
		$isLongTerm = $rules->isLongTermHolding($holdingDays);
		$isLossDeductible = $rules->isLossDeductible($holdingDays);

		if ($gain->isNegative()) {
			if ($isLossDeductible) {
				$harvestNow[] = $this->buildSuggestion(
					$asset,
					$assetData,
					$holdingDays,
					$daysUntilLongTerm,
					$gain,
					$gain->abs(),
					$rate,
					$longTermDays !== null
						? TaxOptimizationRationaleEnum::HarvestBeforeLongTerm
						: TaxOptimizationRationaleEnum::HarvestGenericLoss,
					$holdingVariesByBroker,
				);
				return;
			}

			$lossNoLongerDeductible[] = $this->buildSuggestion(
				$asset,
				$assetData,
				$holdingDays,
				$daysUntilLongTerm,
				$gain,
				new Decimal(0),
				$rate,
				TaxOptimizationRationaleEnum::LossNoLongerDeductible,
				$holdingVariesByBroker,
			);
			return;
		}

		if ($isLongTerm) {
			$alreadyTaxFree[] = $this->buildSuggestion(
				$asset,
				$assetData,
				$holdingDays,
				$daysUntilLongTerm,
				$gain,
				new Decimal(0),
				$rate,
				TaxOptimizationRationaleEnum::AlreadyTaxFree,
				$holdingVariesByBroker,
			);
			return;
		}

		if ($daysUntilLongTerm !== null && $daysUntilLongTerm <= self::HoldForTaxFreeGainThresholdDays) {
			$holdForTaxFreeGain[] = $this->buildSuggestion(
				$asset,
				$assetData,
				$holdingDays,
				$daysUntilLongTerm,
				$gain,
				$gain,
				$rate,
				TaxOptimizationRationaleEnum::HoldForTaxFreeGain,
				$holdingVariesByBroker,
			);
			return;
		}

		$winningShortTerm[] = $this->buildSuggestion(
			$asset,
			$assetData,
			$holdingDays,
			$daysUntilLongTerm,
			$gain,
			null,
			$rate,
			TaxOptimizationRationaleEnum::WinningShortTerm,
			$holdingVariesByBroker,
		);
	}

	private function buildSuggestion(
		Asset $asset,
		AssetDataDto $assetData,
		int $holdingDays,
		?int $daysUntilLongTerm,
		Decimal $gain,
		?Decimal $taxableBase,
		?Decimal $rate,
		TaxOptimizationRationaleEnum $rationale,
		bool $holdingVariesByBroker,
	): TaxOptimizationSuggestionDto {
		$ticker = $asset->ticker;
		$estimatedTaxImpact = $taxableBase !== null && $rate !== null ? $taxableBase->mul($rate) : null;

		return new TaxOptimizationSuggestionDto(
			assetId: $asset->id,
			tickerTicker: $ticker->ticker,
			tickerName: $ticker->name,
			tickerLogo: $ticker->logo,
			firstBuyDate: $assetData->firstTransactionActionCreated->format('Y-m-d'),
			holdingPeriodDays: $holdingDays,
			daysUntilLongTerm: $daysUntilLongTerm,
			units: $assetData->units,
			marketValue: $assetData->value,
			costBasis: $assetData->transactionValueDefaultCurrency,
			unrealizedGainLoss: $gain,
			estimatedTaxImpact: $estimatedTaxImpact,
			rationale: $rationale,
			holdingVariesByBroker: $holdingVariesByBroker,
		);
	}

	/** @param list<Transaction> $transactions */
	private function hasMultipleBuyBrokers(array $transactions): bool
	{
		$brokers = [];
		foreach ($transactions as $transaction) {
			if ($transaction->actionType !== TransactionActionTypeEnum::Buy) {
				continue;
			}
			$brokers[(string) ($transaction->brokerId ?? '')] = true;
			if (count($brokers) > 1) {
				return true;
			}
		}
		return false;
	}

	/** @param list<TaxOptimizationSuggestionDto> $suggestions */
	private function sumTaxImpact(array $suggestions): Decimal
	{
		$total = new Decimal(0);
		foreach ($suggestions as $suggestion) {
			if ($suggestion->estimatedTaxImpact !== null) {
				$total = $total->add($suggestion->estimatedTaxImpact);
			}
		}
		return $total;
	}
}
