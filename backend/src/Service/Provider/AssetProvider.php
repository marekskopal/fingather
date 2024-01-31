<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\AssetRepository;
use FinGather\Model\Repository\SplitRepository;
use FinGather\Service\Provider\Dto\AssetPropertiesDto;
use Safe\DateTimeImmutable;

class AssetProvider
{
	public function __construct(
		private readonly AssetRepository $assetRepository,
		private readonly TransactionProvider $transactionProvider,
		private readonly SplitRepository $splitRepository,
		private readonly TickerDataProvider $tickerDataProvider,
		private readonly ExchangeRateProvider $exchangeRateProvider,
		private readonly GroupProvider $groupProvider,
	) {
	}

	/** @return array<int, Asset> */
	public function getAssets(User $user, Portfolio $portfolio): array
	{
		return $this->assetRepository->findAssets($user->getId(), $portfolio->getId());
	}

	public function countAssets(User $user, ?Portfolio $portfolio = null): int
	{
		return $this->assetRepository->countAssets($user->getId(), $portfolio?->getId());
	}

	/** @return array<int, Asset> */
	public function getOpenAssets(User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): array
	{
		return $this->assetRepository->findOpenAssets($user->getId(), $dateTime);
	}

	/** @return array<int, Asset> */
	public function getOpenAssetsByGroup(Group $group, User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): array
	{
		return $this->assetRepository->findOpenAssetsByGroup($user->getId(), $group->getId(), $dateTime);
	}

	/** @return array<int, Asset> */
	public function getClosedAssets(User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): array
	{
		return $this->assetRepository->findClosedAssets($user->getId(), $dateTime);
	}

	/** @return array<int, Asset> */
	public function getWatchedAssets(User $user, Portfolio $portfolio): array
	{
		return $this->assetRepository->findWatchedAssets($user->getId());
	}

	public function getAsset(User $user, int $assetId): ?Asset
	{
		return $this->assetRepository->findAsset($assetId, $user->getId());
	}

	public function getAssetProperties(User $user, Portfolio $portfolio, Asset $asset, DateTimeImmutable $dateTime): ?AssetPropertiesDto
	{
		$transactions = $this->transactionProvider->getTransactions($user, $portfolio, $asset, $dateTime);
		if (count($transactions) === 0) {
			return null;
		}

		$splits = $this->splitRepository->findSplits($asset->getTicker()->getId());

		$transactionValue = new Decimal(0);
		$transactionValueDefaultCurrency = new Decimal(0);
		$units = new Decimal(0);
		$dividendTotal = new Decimal(0);

		$tickerCurrency = $asset->getTicker()->getCurrency();

		foreach ($transactions as $transaction) {
			if (TransactionActionTypeEnum::from($transaction->getActionType()) === TransactionActionTypeEnum::Dividend) {
				$dividendExchangeRate = $this->exchangeRateProvider->getExchangeRate(
					DateTimeImmutable::createFromRegular($transaction->getActionCreated()),
					$transaction->getCurrency(),
					$tickerCurrency,
				);

				$dividendTotal = $dividendTotal->add((new Decimal($transaction->getPrice()))->mul($dividendExchangeRate->getRate()));

				continue;
			}

			$splitFactor = new Decimal(1);

			foreach ($splits as $split) {
				if ($split->getDate() >= $transaction->getActionCreated() && $split->getDate() <= $dateTime) {
					$splitFactor = $splitFactor->mul(new Decimal($split->getFactor()));
				}
			}

			$transactionUnits = new Decimal($transaction->getUnits());
			$transactionPriceUnit = new Decimal($transaction->getPrice());

			$units = $units->add($transactionUnits->mul($splitFactor));

			//if close position, start from zero
			if ($units->toFloat() === 0.0) {
				$transactionValue = new Decimal(0);
				$transactionValueDefaultCurrency = new Decimal(0);

				continue;
			}

			$transactionSum = $transactionUnits->mul($transactionPriceUnit);

			if ($tickerCurrency->getId() !== $transaction->getCurrency()->getId()) {
				$transactionExchangeRate = $this->exchangeRateProvider->getExchangeRate(
					DateTimeImmutable::createFromRegular($transaction->getActionCreated()),
					$transaction->getCurrency(),
					$tickerCurrency,
				);

				$transactionSum = $transactionSum->mul($transactionExchangeRate->getRate());
			}

			$transactionValue = $transactionValue->add($transactionSum);

			$transactionExchangeRateDefaultCurrency = $this->exchangeRateProvider->getExchangeRate(
				DateTimeImmutable::createFromRegular($transaction->getActionCreated()),
				$tickerCurrency,
				$user->getDefaultCurrency(),
			);

			$transactionValueDefaultCurrency = $transactionValueDefaultCurrency->add(
				$transactionSum->mul($transactionExchangeRateDefaultCurrency->getRate()),
			);
		}

		$price = new Decimal(0);

		$lastTickerData = $this->tickerDataProvider->getLastTickerData($asset->getTicker(), $dateTime);
		if ($lastTickerData !== null) {
			$price = new Decimal($lastTickerData->getClose());
		}

		$exchangeRate = $this->exchangeRateProvider->getExchangeRate(
			DateTimeImmutable::createFromRegular($dateTime),
			$tickerCurrency,
			$user->getDefaultCurrency(),
		);

		$exchangeRateDecimal = new Decimal($exchangeRate->getRate());

		$value = $units->mul($price);
		$gain = $value->sub($transactionValue);
		$gainDefaultCurrency = $gain->mul($exchangeRateDecimal);
		$dividendGainDefaultCurrency = $dividendTotal->mul($exchangeRateDecimal);
		$fxImpact = $transactionValue->mul($exchangeRateDecimal)->sub($transactionValueDefaultCurrency);

		if ($transactionValue->compareTo(0) === 0) {
			$gainPercentage = 0.0;
			$dividendGainPercentage = 0.0;
			$fxImpactPercentage = 0.0;
		} else {
			$gainPercentage = round(($gain->div($transactionValue)->mul(100))->toFloat(), 2);
			$dividendGainPercentage = round(($dividendTotal->div($transactionValue)->mul(100))->toFloat(), 2);
			$fxImpactPercentage = round(($fxImpact->div($transactionValueDefaultCurrency)->mul(100))->toFloat(), 2);
		}

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

	public function createAsset(User $user, Portfolio $portfolio, Ticker $ticker): Asset
	{
		$group = $this->groupProvider->getOthersGroup($user, $portfolio);

		$asset = new Asset(
			user: $user,
			portfolio: $portfolio,
			ticker: $ticker,
			group: $group,
			transactions: [],
		);

		$this->assetRepository->persist($asset);

		return $asset;
	}
}
