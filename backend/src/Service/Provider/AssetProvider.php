<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use Decimal\Decimal;
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

	public function getAssetProperties(User $user, Asset $asset, DateTime $dateTime): ?AssetPropertiesDto
	{
		$transactions = $this->transactionRepository->findAssetTransactions($asset->getId(), $dateTime);
		if (count($transactions) === 0) {
			return null;
		}

		$splits = $this->splitRepository->findSplits($asset->getTicker()->getId());

		$transactionValue = new Decimal(0);
		$transactionTotal = new Decimal(0);
		$units = new Decimal(0);

		foreach ($transactions as $transaction) {
			$splitFactor = new Decimal(1);

			foreach ($splits as $split) {
				if ($split->getDate() >= $transaction->getCreated() && $split->getDate() <= $dateTime) {
					$splitFactor = $splitFactor->mul(new Decimal($split->getFactor()));
				}
			}

			$transactionUnits = (new Decimal($transaction->getUnits()))->mul($splitFactor);
			$transactionPriceUnit = (new Decimal($transaction->getPriceUnit()))->div($splitFactor);

			$units = $units->add($transactionUnits);

			//if close position, start from zero
			if ($units->toFloat() === 0.0) {
				$transactionValue = new Decimal(0);
				$transactionTotal = new Decimal(0);

				continue;
			}

			$transactionValue = $transactionValue->add($transactionUnits->mul($transactionPriceUnit));
			$transactionTotal = $transactionTotal->add(
				$transactionUnits->mul($transactionPriceUnit)->mul(new Decimal($transaction->getExchangeRate()))
			);
		}

		$dividendTotal = new Decimal(0);
		$dividends = $this->dividendRepository->findDividends($asset->getId(), $dateTime);
		foreach ($dividends as $dividend) {
			$dividendTotal = $dividendTotal->add(new Decimal($dividend->getPriceNet()));
		}

		$price = new Decimal(0);

		$lastTickerData = $this->tickerDataProvider->getLastTickerData($asset->getTicker(), $dateTime);
		if ($lastTickerData !== null) {
			$price = new Decimal($lastTickerData->getClose());
		}

		$currencyTo = $asset->getTicker()->getCurrency();

		$exchangeRateDateTime = DateTimeImmutable::createFromMutable($dateTime);
		$exchangeRateDateTime = $exchangeRateDateTime->setTime(0, 0);
		$exchangeRate = $this->exchangeRateProvider->getExchangeRate(
			$exchangeRateDateTime,
			$user->getDefaultCurrency(),
			$currencyTo
		);

		$exchangeRateDecimal = new Decimal($exchangeRate->getRate());

		$value = $units->mul($price);
		$gain = $value->sub($transactionValue);
		$gainDefaultCurrency = $gain->mul($exchangeRateDecimal);
		$gainPercentage = round(($gain->div($transactionValue)->mul(100))->toFloat(), 2);
		$dividendGainDefaultCurrency = $dividendTotal->mul($exchangeRateDecimal);
		$dividendGainPercentage = round(($dividendTotal->div($value)->mul(100))->toFloat(), 2);
		$fxImpact = $transactionValue->mul($exchangeRateDecimal)->sub($transactionTotal);
		$fxImpactPercentage = round(($fxImpact->div($transactionTotal)->mul(100))->toFloat(), 2);

		return new AssetPropertiesDto(
			price: $price,
			units: $units,
			value: $value->mul($exchangeRateDecimal),
			transactionValue: $transactionTotal,
			gain: $gain,
			gainDefaultCurrency: $gainDefaultCurrency,
			gainPercentage: $gainPercentage,
			dividendGain: $dividendTotal,
			dividendGainDefaultCurrency: $dividendGainDefaultCurrency,
			dividendGainPercentage: $dividendGainPercentage,
			fxImpact: $fxImpact,
			fxImpactPercentage: $fxImpactPercentage,
			return: $gainDefaultCurrency->add($dividendGainDefaultCurrency)->add($fxImpact),
			returnPercentage: round($gainPercentage + $dividendGainPercentage + $fxImpactPercentage, 2),
		);
	}
}
