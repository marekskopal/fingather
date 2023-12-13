<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Dto\BrokerDto;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Broker;
use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\AssetRepository;
use FinGather\Model\Repository\BrokerRepository;
use FinGather\Model\Repository\CurrencyRepository;
use FinGather\Model\Repository\SplitRepository;
use FinGather\Model\Repository\TransactionRepository;
use FinGather\Service\Provider\Dto\AssetPropertiesDto;
use Safe\DateTime;

class AssetProvider
{
	public function __construct(
		private readonly AssetRepository $assetRepository,
		private readonly TransactionRepository $transactionRepository,
		private readonly SplitRepository $splitRepository,
		private readonly TickerDataProvider $tickerDataProvider,
		private readonly CurrencyRepository $currencyRepository,
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

		$price = 0;

		$lastTickerData = $this->tickerDataProvider->getLastTickerData($asset->getTicker(), $dateTime);
		if ($lastTickerData !== null) {
			$price = $lastTickerData->getClose();
		}

		$currencyTo = $asset->getTicker()->getCurrency();
		$exchangeRate =
		/*



        var currencyTo = _currencyRepository.FindOne(asset.AssetTicker.CurrencyId);
        var exchangeRate = await _exchangeRateProvider.GetExchangeRate(date, user.DefaultCurrency, currencyTo);

        var value = assetDto.Units * assetDto.Price;

        assetDto.Gain = value - transactionValue;
        assetDto.GainDefaultCurrency = assetDto.Gain * exchangeRate.Rate;
        assetDto.GainPercentage = Math.Round(assetDto.Gain / transactionValue * 100, 2);

        decimal dividendTotal = 0;
        var dividends = _dividendRepository.FindBy(d => d.AssetId == asset.Id && d.PaidDate <= date);
        foreach (var dividend in dividends)
        {
			dividendTotal += dividend.PriceNet;
		}

        assetDto.DividendGain = dividendTotal;
        assetDto.DividendGainDefaultCurrency = dividendTotal * exchangeRate.Rate;
        assetDto.DividendGainPercentage = Math.Round(dividendTotal / value * 100, 2);

        assetDto.FxImpact = transactionValue * exchangeRate.Rate - transactionTotal;
        assetDto.FxImpactPercentage = Math.Round(assetDto.FxImpact / transactionTotal * 100, 2);

        assetDto.TransactionValue = Math.Round(transactionTotal, 2);

        assetDto.Value = Math.Round(value * exchangeRate.Rate, 2);

        assetDto.Return = assetDto.GainDefaultCurrency + assetDto.DividendGainDefaultCurrency + assetDto.FxImpact;
        assetDto.ReturnPercentage = assetDto.GainPercentage + assetDto.DividendGainPercentage + assetDto.FxImpactPercentage;

        return assetDto;
		*/

		return new AssetPropertiesDto(
			price: $price,
			units: $units,
		)
	}
}
