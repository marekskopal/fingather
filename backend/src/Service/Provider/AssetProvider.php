<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

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

		$transactionValue = 0;
		$transactionTotal = 0;
		$units = 0;

		foreach ($transactions as $transaction) {
			$splitFactor = 1;

			foreach ($splits as $split) {
				if ($split->getDate() >= $transaction->getCreated() && $split->getDate() <= $dateTime) {
					$splitFactor *= $split->getFactor();
				}
			}

			$transactionUnits = $transaction->getUnits() * $splitFactor;
			$transactionPriceUnit = $transaction->getPriceUnit() / $splitFactor;

			$units += $transactionUnits;

			//if close position, start from zero
			if ($units === 0) {
				$transactionValue = 0;
				$transactionTotal = 0;

				continue;
			}

			$transactionValue += $transactionUnits * $transactionPriceUnit;
			$transactionTotal += $transactionUnits * $transactionPriceUnit * $transaction->getExchangeRate();
		}

		$dividendTotal = 0;
		$dividends = $this->dividendRepository->findDividends($asset->getId(), $dateTime);
		foreach ($dividends as $dividend) {
			$dividendTotal += $dividend->getPriceNet();
		}

		$price = 0;

		$lastTickerData = $this->tickerDataProvider->getLastTickerData($asset->getTicker(), $dateTime);
		if ($lastTickerData !== null) {
			$price = $lastTickerData->getClose();
		}

		$currencyTo = $asset->getTicker()->getCurrency();

		$exchangeRateDateTime = DateTimeImmutable::createFromMutable($dateTime);
		$exchangeRateDateTime = $exchangeRateDateTime->setTime(0, 0);
		$exchangeRate = $this->exchangeRateProvider->getExchangeRate(
			$exchangeRateDateTime,
			$asset->getUser()->getDefaultCurrency(),
			$currencyTo
		);

		$value = $units * $price;
		$gain = $value - $transactionValue;
		$gainDefaultCurrency = $gain * $exchangeRate->getRate();
		$gainPercentage = round($gain / $transactionValue * 100, 2);
		$dividendGainDefaultCurrency = $dividendTotal * $exchangeRate->getRate();
		$dividendGainPercentage = round($dividendTotal / $value * 100, 2);
		$fxImpact = $transactionValue * $exchangeRate->getRate() - $transactionTotal;
		$fxImpactPercentage = round($fxImpact / $transactionTotal * 100, 2);

		return new AssetPropertiesDto(
			price: $price,
			units: $units,
			value: round($value * $exchangeRate->getRate(), 2),
			transactionValue: round($transactionTotal, 2),
			gain: $gain,
			gainDefaultCurrency: $gainDefaultCurrency,
			gainPercentage: $gainPercentage,
			dividendGain: $dividendTotal,
			dividendGainDefaultCurrency: $dividendGainDefaultCurrency,
			dividendGainPercentage: $dividendGainPercentage,
			fxImpact: $fxImpact,
			fxImpactPercentage: $fxImpactPercentage,
			return: $gainDefaultCurrency + $dividendGainDefaultCurrency + $fxImpact,
			returnPercentage: $gainPercentage + $dividendGainPercentage + $fxImpactPercentage,
		);
	}
}
