<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\AssetRepository;
use FinGather\Model\Repository\SplitRepository;
use FinGather\Model\Repository\TransactionRepository;
use FinGather\Service\Provider\Dto\AssetPropertiesDto;
use Safe\DateTimeImmutable;

class AssetProvider
{
	public function __construct(
		private readonly AssetRepository $assetRepository,
		private readonly TransactionRepository $transactionRepository,
		private readonly SplitRepository $splitRepository,
		private readonly TickerDataProvider $tickerDataProvider,
		private readonly ExchangeRateProvider $exchangeRateProvider,
		private readonly DividendProvider $dividendProvider,
	) {
	}

	/** @return array<int, Asset> */
	public function getAssets(User $user, DateTimeImmutable $dateTime): array
	{
		return $this->assetRepository->findOpenAssets($user->getId(), $dateTime);
	}

	/** @return array<int, Asset> */
	public function getAssetsByGroup(Group $group, User $user, DateTimeImmutable $dateTime): array
	{
		return $this->assetRepository->findOpenAssetsByGroup($user->getId(), $group->getId(), $dateTime);
	}

	public function getAsset(User $user, int $assetId): ?Asset
	{
		return $this->assetRepository->findAsset($assetId, $user->getId());
	}

	public function getAssetProperties(User $user, Asset $asset, DateTimeImmutable $dateTime): ?AssetPropertiesDto
	{
		$transactions = $this->transactionRepository->findAssetTransactions($user->getId(), $asset->getId(), $dateTime);
		if (count($transactions) === 0) {
			return null;
		}

		$splits = $this->splitRepository->findSplits($asset->getTicker()->getId());

		$transactionValue = new Decimal(0);
		$transactionValueDefaultCurrency = new Decimal(0);
		$units = new Decimal(0);

		$tickerCurrency = $asset->getTicker()->getCurrency();

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
				$transactionValueDefaultCurrency = new Decimal(0);

				continue;
			}

			$transactionSum = $transactionUnits->mul($transactionPriceUnit);

			if ($tickerCurrency->getId() !== $transaction->getCurrency()->getId()) {
				$transactionExchangeRate = $this->exchangeRateProvider->getExchangeRate(
					DateTimeImmutable::createFromRegular($transaction->getCreated())->setTime(0, 0),
					$transaction->getCurrency(),
					$tickerCurrency,
				);

				$transactionSum = $transactionSum->mul($transactionExchangeRate->getRate());
			}

			$transactionValue = $transactionValue->add($transactionSum);

			$transactionExchangeRateDefaultCurrency = $this->exchangeRateProvider->getExchangeRate(
				DateTimeImmutable::createFromRegular($transaction->getCreated())->setTime(0, 0),
				$tickerCurrency,
				$user->getDefaultCurrency(),
			);

			$transactionValueDefaultCurrency = $transactionValueDefaultCurrency->add(
				$transactionSum->mul($transactionExchangeRateDefaultCurrency->getRate())
			);
		}

		$dividendTotal = new Decimal(0);
		$dividends = $this->dividendProvider->getAssetDividends($user, $asset, $dateTime);
		foreach ($dividends as $dividend) {
			$dividendExchangeRate = $this->exchangeRateProvider->getExchangeRate(
				DateTimeImmutable::createFromRegular($dividend->getPaidDate())->setTime(0, 0),
				$dividend->getCurrency(),
				$tickerCurrency,
			);

			$dividendTotal = $dividendTotal->add((new Decimal($dividend->getPriceNet()))->mul($dividendExchangeRate->getRate()));
		}

		$price = new Decimal(0);

		$lastTickerData = $this->tickerDataProvider->getLastTickerData($asset->getTicker(), $dateTime);
		if ($lastTickerData !== null) {
			$price = new Decimal($lastTickerData->getClose());
		}

		$exchangeRate = $this->exchangeRateProvider->getExchangeRate(
			DateTimeImmutable::createFromRegular($dateTime)->setTime(0, 0),
			$tickerCurrency,
			$user->getDefaultCurrency(),
		);

		$exchangeRateDecimal = new Decimal($exchangeRate->getRate());

		$value = $units->mul($price);
		$gain = $value->sub($transactionValue);
		$gainDefaultCurrency = $gain->mul($exchangeRateDecimal);
		$gainPercentage = round(($gain->div($transactionValue)->mul(100))->toFloat(), 2);
		$dividendGainDefaultCurrency = $dividendTotal->mul($exchangeRateDecimal);
		$dividendGainPercentage = round(($dividendTotal->div($transactionValue)->mul(100))->toFloat(), 2);
		$fxImpact = $transactionValue->mul($exchangeRateDecimal)->sub($transactionValueDefaultCurrency);
		$fxImpactPercentage = round(($fxImpact->div($transactionValueDefaultCurrency)->mul(100))->toFloat(), 2);

		return new AssetPropertiesDto(
			price: $price,
			units: $units,
			value: $value->mul($exchangeRateDecimal),
			transactionValue: $transactionValueDefaultCurrency,
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
