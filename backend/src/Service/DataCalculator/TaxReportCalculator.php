<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\TaxReportDividendsByCountryDto;
use FinGather\Service\DataCalculator\Dto\TaxReportDividendsDto;
use FinGather\Service\DataCalculator\Dto\TaxReportDividendTransactionDto;
use FinGather\Service\DataCalculator\Dto\TaxReportDto;
use FinGather\Service\DataCalculator\Dto\TaxReportUnrealizedDto;
use FinGather\Service\DataCalculator\Dto\TaxReportUnrealizedPositionDto;
use FinGather\Service\Provider\AssetProvider;
use FinGather\Service\Provider\CurrentTransactionProvider;

final class TaxReportCalculator
{
	public function __construct(
		private readonly CurrentTransactionProvider $currentTransactionProvider,
		private readonly AssetProvider $assetProvider,
		private readonly AssetDataCalculator $assetDataCalculator,
		private readonly TaxReportRealizedGainsCalculator $realizedGainsCalculator,
	) {
	}

	public function calculate(User $user, Portfolio $portfolio, int $year): TaxReportDto
	{
		$yearStart = new DateTimeImmutable($year . '-01-01');
		$yearEnd = new DateTimeImmutable($year . '-12-31 23:59:59');

		return new TaxReportDto(
			year: $year,
			realizedGains: $this->realizedGainsCalculator->calculate($user, $portfolio, $yearStart, $yearEnd),
			unrealizedPositions: $this->calculateUnrealizedPositions($user, $portfolio, $yearEnd),
			dividends: $this->calculateDividends($user, $portfolio, $yearStart, $yearEnd),
			totalFees: $this->calculateTotalFees($user, $portfolio, $yearStart, $yearEnd),
			totalTaxes: $this->calculateTotalTaxes($user, $portfolio, $yearStart, $yearEnd),
		);
	}

	private function calculateUnrealizedPositions(User $user, Portfolio $portfolio, DateTimeImmutable $yearEnd): TaxReportUnrealizedDto
	{
		$assets = $this->assetProvider->getAssets(user: $user, portfolio: $portfolio, dateTime: $yearEnd);

		$positions = [];
		$totalMarketValue = new Decimal(0);
		$totalCostBasis = new Decimal(0);
		$totalGainLoss = new Decimal(0);

		foreach ($assets as $asset) {
			$assetData = $this->assetDataCalculator->calculate(user: $user, portfolio: $portfolio, asset: $asset, dateTime: $yearEnd,);

			if ($assetData === null || !$assetData->isOpen()) {
				continue;
			}

			$positions[] = new TaxReportUnrealizedPositionDto(
				tickerTicker: $asset->ticker->ticker,
				tickerName: $asset->ticker->name,
				firstBuyDate: $assetData->firstTransactionActionCreated->format('Y-m-d'),
				holdingPeriodDays: (int) $yearEnd->diff($assetData->firstTransactionActionCreated)->days,
				units: $assetData->units,
				buyPrice: $assetData->averagePriceDefaultCurrency,
				costBasis: $assetData->transactionValueDefaultCurrency,
				marketValue: $assetData->value,
				gainLoss: $assetData->gainDefaultCurrency,
			);

			$totalMarketValue = $totalMarketValue->add($assetData->value);
			$totalCostBasis = $totalCostBasis->add($assetData->transactionValueDefaultCurrency);
			$totalGainLoss = $totalGainLoss->add($assetData->gainDefaultCurrency);
		}

		return new TaxReportUnrealizedDto(
			totalMarketValue: $totalMarketValue,
			totalCostBasis: $totalCostBasis,
			totalGainLoss: $totalGainLoss,
			positions: $positions,
		);
	}

	private function calculateDividends(
		User $user,
		Portfolio $portfolio,
		DateTimeImmutable $yearStart,
		DateTimeImmutable $yearEnd,
	): TaxReportDividendsDto {
		$allTransactionsByAsset = $this->currentTransactionProvider->loadTransactions(user: $user, portfolio: $portfolio);

		$transactions = [];
		$totalGross = new Decimal(0);
		$totalTax = new Decimal(0);
		$totalNet = new Decimal(0);

		foreach ($allTransactionsByAsset as $assetTransactions) {
			$dividendTaxes = $this->collectDividendTaxes($assetTransactions, $yearStart, $yearEnd);

			$this->processDividendTransactions(
				$assetTransactions,
				$yearStart,
				$yearEnd,
				$dividendTaxes,
				$transactions,
				$totalGross,
				$totalTax,
				$totalNet,
			);
		}

		$dividendsByCountry = $this->aggregateDividendsByCountry($transactions);

		return new TaxReportDividendsDto(
			totalGross: $totalGross,
			totalTax: $totalTax,
			totalNet: $totalNet,
			dividendsByCountry: $dividendsByCountry,
			transactions: $transactions,
		);
	}

	/**
	 * @param list<Transaction> $assetTransactions
	 * @return array<string, Decimal>
	 */
	private function collectDividendTaxes(array $assetTransactions, DateTimeImmutable $yearStart, DateTimeImmutable $yearEnd): array
	{
		$dividendTaxes = [];

		foreach ($assetTransactions as $transaction) {
			if ($transaction->actionType !== TransactionActionTypeEnum::DividendTax) {
				continue;
			}

			if ($transaction->actionCreated < $yearStart || $transaction->actionCreated > $yearEnd) {
				continue;
			}

			$dateKey = $transaction->actionCreated->format('Y-m-d');
			$dividendTaxes[$dateKey] = ($dividendTaxes[$dateKey] ?? new Decimal(0))->add($transaction->priceDefaultCurrency);
		}

		return $dividendTaxes;
	}

	/**
	 * @param list<Transaction> $assetTransactions
	 * @param array<string, Decimal> $dividendTaxes
	 * @param list<TaxReportDividendTransactionDto> $transactions
	 */
	private function processDividendTransactions(
		array $assetTransactions,
		DateTimeImmutable $yearStart,
		DateTimeImmutable $yearEnd,
		array $dividendTaxes,
		array &$transactions,
		Decimal &$totalGross,
		Decimal &$totalTax,
		Decimal &$totalNet,
	): void {
		foreach ($assetTransactions as $transaction) {
			if ($transaction->actionType !== TransactionActionTypeEnum::Dividend) {
				continue;
			}

			if ($transaction->actionCreated < $yearStart || $transaction->actionCreated > $yearEnd) {
				continue;
			}

			$ticker = $transaction->asset->ticker;
			$grossAmount = $transaction->priceDefaultCurrency;
			$dateKey = $transaction->actionCreated->format('Y-m-d');
			$tax = ($dividendTaxes[$dateKey] ?? new Decimal(0))->abs();
			$netAmount = $grossAmount->sub($tax);

			$transactions[] = new TaxReportDividendTransactionDto(
				tickerTicker: $ticker->ticker,
				tickerName: $ticker->name,
				countryName: $ticker->country->name,
				countryIsoCode: $ticker->country->isoCode,
				date: $dateKey,
				grossAmount: $grossAmount,
				tax: $tax,
				netAmount: $netAmount,
			);

			$totalGross = $totalGross->add($grossAmount);
			$totalTax = $totalTax->add($tax);
			$totalNet = $totalNet->add($netAmount);

			unset($dividendTaxes[$dateKey]);
		}
	}

	/**
	 * @param list<TaxReportDividendTransactionDto> $transactions
	 * @return list<TaxReportDividendsByCountryDto>
	 */
	private function aggregateDividendsByCountry(array $transactions): array
	{
		/**
		 * @var array<string, array{
		 *     countryName: string,
		 *     gross: Decimal,
		 *     tax: Decimal,
		 *     net: Decimal
		 * }> $byCountry
		 */
		$byCountry = [];

		foreach ($transactions as $transaction) {
			$isoCode = $transaction->countryIsoCode;

			if (!isset($byCountry[$isoCode])) {
				$byCountry[$isoCode] = [
					'countryName' => $transaction->countryName,
					'gross' => new Decimal(0),
					'tax' => new Decimal(0),
					'net' => new Decimal(0),
				];
			}

			$byCountry[$isoCode]['gross'] = $byCountry[$isoCode]['gross']->add($transaction->grossAmount);
			$byCountry[$isoCode]['tax'] = $byCountry[$isoCode]['tax']->add($transaction->tax);
			$byCountry[$isoCode]['net'] = $byCountry[$isoCode]['net']->add($transaction->netAmount);
		}

		$result = [];

		foreach ($byCountry as $isoCode => $data) {
			$result[] = new TaxReportDividendsByCountryDto(
				countryName: $data['countryName'],
				countryIsoCode: $isoCode,
				totalGross: $data['gross'],
				totalTax: $data['tax'],
				totalNet: $data['net'],
			);
		}

		return $result;
	}

	private function calculateTotalFees(
		User $user,
		Portfolio $portfolio,
		DateTimeImmutable $yearStart,
		DateTimeImmutable $yearEnd,
	): Decimal {
		$allTransactionsByAsset = $this->currentTransactionProvider->loadTransactions(user: $user, portfolio: $portfolio);
		$total = new Decimal(0);

		foreach ($allTransactionsByAsset as $assetTransactions) {
			foreach ($assetTransactions as $transaction) {
				if ($transaction->actionCreated >= $yearStart && $transaction->actionCreated <= $yearEnd) {
					$total = $total->add($transaction->feeDefaultCurrency);
				}
			}
		}

		return $total;
	}

	private function calculateTotalTaxes(
		User $user,
		Portfolio $portfolio,
		DateTimeImmutable $yearStart,
		DateTimeImmutable $yearEnd,
	): Decimal {
		$allTransactionsByAsset = $this->currentTransactionProvider->loadTransactions(user: $user, portfolio: $portfolio);
		$total = new Decimal(0);

		foreach ($allTransactionsByAsset as $assetTransactions) {
			foreach ($assetTransactions as $transaction) {
				if ($transaction->actionCreated >= $yearStart && $transaction->actionCreated <= $yearEnd) {
					$total = $total->add($transaction->taxDefaultCurrency);
				}
			}
		}

		return $total;
	}
}
