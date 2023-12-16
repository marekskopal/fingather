<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\AssetRepository;
use FinGather\Model\Repository\DividendRepository;
use FinGather\Model\Repository\SplitRepository;
use FinGather\Model\Repository\TransactionRepository;
use FinGather\Service\Provider\Dto\AssetPropertiesDto;
use Safe\DateTime;
use Safe\DateTimeImmutable;

class AssetProvider
{
	public function __construct(
		private readonly AssetRepository $assetRepository,
		private readonly TransactionRepository $transactionRepository,
		private readonly SplitRepository $splitRepository,
		private readonly TickerDataProvider $tickerDataProvider,
		private readonly ExchangeRateProvider $exchangeRateProvider,
		private readonly DividendRepository $dividendRepository,
	) {
	}

	/** @return iterable<Asset> */
	public function getAssets(User $user, DateTime $dateTime): iterable
	{
		return $this->assetRepository->findOpenAssets($user->getId(), $dateTime);
	}

	public function getAssetProperties(Asset $asset, DateTime $dateTime): ?AssetPropertiesDto
	{
		$transactions = $this->transactionRepository->findAssetTransactions($asset->getId(), $dateTime);
		if (count($transactions) === 0) {
			return null;
		}

		$splits = $this->splitRepository->findSplits($asset->getTicker()->getId());

		$transactionValue = BigDecimal::of(0);
		$transactionTotal = BigDecimal::of(0);
		$units = BigDecimal::of(0);

		foreach ($transactions as $transaction) {
			$splitFactor = BigDecimal::of(1);

			foreach ($splits as $split) {
				if ($split->getDate() >= $transaction->getCreated() && $split->getDate() <= $dateTime) {
					$splitFactor = $splitFactor->multipliedBy(BigDecimal::of($split->getFactor()));
				}
			}

			$transactionUnits = BigDecimal::of($transaction->getUnits())->multipliedBy($splitFactor);
			$transactionPriceUnit = BigDecimal::of($transaction->getPriceUnit())->dividedBy($splitFactor);
			\ray($transactionUnits);
			\ray($transactionPriceUnit);

			$units = $units->plus($transactionUnits);

			//if close position, start from zero
			if ($units->toFloat() === 0.0) {
				$transactionValue = BigDecimal::of(0);
				$transactionTotal = BigDecimal::of(0);

				continue;
			}

			$transactionValue = $transactionValue->plus($transactionUnits->multipliedBy($transactionPriceUnit));
			$transactionTotal = $transactionTotal->plus($transactionUnits->multipliedBy($transactionPriceUnit)->multipliedBy(BigDecimal::of($transaction->getExchangeRate())));
		}

		$dividendTotal = BigDecimal::of(0);
		$dividends = $this->dividendRepository->findDividends($asset->getId(), $dateTime);
		foreach ($dividends as $dividend) {
			$dividendTotal = $dividendTotal->plus(BigDecimal::of($dividend->getPriceNet()));
		}

		$price = BigDecimal::of(0);

		$lastTickerData = $this->tickerDataProvider->getLastTickerData($asset->getTicker(), $dateTime);
		if ($lastTickerData !== null) {
			$price = BigDecimal::of($lastTickerData->getClose());
		}

		$currencyTo = $asset->getTicker()->getCurrency();

		$exchangeRateDateTime = DateTimeImmutable::createFromMutable($dateTime);
		$exchangeRateDateTime = $exchangeRateDateTime->setTime(0, 0);
		$exchangeRate = $this->exchangeRateProvider->getExchangeRate(
			$exchangeRateDateTime,
			$asset->getUser()->getDefaultCurrency(),
			$currencyTo
		);

		$exchangeRateDecimal = BigDecimal::of($exchangeRate->getRate());

		$value = $units->multipliedBy($price);
		$gain = $value->minus($transactionValue);
		$gainDefaultCurrency = $gain->multipliedBy($exchangeRateDecimal);
		$gainPercentage = round($gain->dividedBy($transactionValue, roundingMode: RoundingMode::HALF_EVEN)->multipliedBy(100)->toFloat(), 2);
		$dividendGainDefaultCurrency = $dividendTotal->multipliedBy($exchangeRateDecimal);
		$dividendGainPercentage = round($dividendTotal->dividedBy($value, roundingMode: RoundingMode::HALF_EVEN)->multipliedBy(100)->toFloat(), 2);
		$fxImpact = $transactionValue->multipliedBy($exchangeRateDecimal)->minus($transactionTotal);
		$fxImpactPercentage = round($fxImpact->dividedBy($transactionTotal, roundingMode: RoundingMode::HALF_EVEN)->multipliedBy(100)->toFloat(), 2);

		return new AssetPropertiesDto(
			price: $price,
			units: $units,
			value: $value->multipliedBy($exchangeRateDecimal),
			transactionValue: $transactionTotal,
			gain: $gain,
			gainDefaultCurrency: $gainDefaultCurrency,
			gainPercentage: $gainPercentage,
			dividendGain: $dividendTotal,
			dividendGainDefaultCurrency: $dividendGainDefaultCurrency,
			dividendGainPercentage: $dividendGainPercentage,
			fxImpact: $fxImpact,
			fxImpactPercentage: $fxImpactPercentage,
			return: $gainDefaultCurrency->plus($dividendGainDefaultCurrency)->plus($fxImpact),
			returnPercentage: round($gainPercentage + $dividendGainPercentage + $fxImpactPercentage, 2),
		);
	}
}
