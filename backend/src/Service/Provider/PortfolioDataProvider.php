<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\Enum\OrderDirectionEnum;
use FinGather\Model\Repository\Enum\TransactionOrderByEnum;
use FinGather\Service\Cache\Cache;
use FinGather\Service\Cache\CacheFactoryInterface;
use FinGather\Service\Cache\CacheStorageEnum;
use FinGather\Service\DataCalculator\DataCalculatorInterface;
use FinGather\Service\DataCalculator\Dto\CalculatedDataDto;
use FinGather\Service\DataCalculator\Dto\PortfolioCashFlowDto;
use FinGather\Service\DataCalculator\MwrCalculatorInterface;
use FinGather\Service\DataCalculator\TwrCalculatorInterface;
use FinGather\Utils\CalculatorUtils;
use FinGather\Utils\DateTimeUtils;
use Psr\Log\LoggerInterface;

final class PortfolioDataProvider implements PortfolioDataProviderInterface
{
	private Cache $cache;

	private const string CacheNamespace = 'portfolio-data';

	public function __construct(
		private readonly DataCalculatorInterface $dataCalculator,
		private readonly AssetProviderInterface $assetProvider,
		private readonly AssetDataProviderInterface $assetDataProvider,
		private readonly TransactionProviderInterface $transactionProvider,
		private readonly TwrCalculatorInterface $twrCalculator,
		private readonly MwrCalculatorInterface $mwrCalculator,
		private readonly LoggerInterface $logger,
		CacheFactoryInterface $cacheFactory,
	) {
		$this->cache = $cacheFactory->create(driver: CacheStorageEnum::Redis, namespace: self::CacheNamespace);
	}

	public function getPortfolioData(User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): CalculatedDataDto
	{
		$dateTime = DateTimeUtils::setEndOfDateTime($dateTime);

		$key = $portfolio->id . '-' . $dateTime->getTimestamp();

		/** @var CalculatedDataDto|null $portfolioData */
		$portfolioData = $this->cache->load($key);
		if ($portfolioData !== null) {
			return $portfolioData;
		}

		$this->logger->debug(
			'Calculating portfolio data for user ' . $user->id . ' and portfolio ' . $portfolio->id . ' and date ' . $dateTime->format(
				'Y-m-d',
			),
		);

		$cashFlows = $this->buildCashFlows($user, $portfolio, $dateTime);

		$firstTransactionDate = $cashFlows !== [] ? $cashFlows[0]->date : null;
		$calculatedData = $this->computeBasicPortfolioData($user, $portfolio, $dateTime, $firstTransactionDate);

		if ($cashFlows !== []) {
			$fromFirstTransactionDays = (int) $dateTime->diff($cashFlows[0]->date)->days;

			$twr = $this->twrCalculator->calculate(
				cashFlows: $cashFlows,
				portfolioValueFetcher: fn (DateTimeImmutable $d): Decimal => $this->getPortfolioValue($user, $portfolio, $d),
				currentValue: $calculatedData->value,
				currentDate: $dateTime,
			);

			$mwr = $this->mwrCalculator->calculate(cashFlows: $cashFlows, endingValue: $calculatedData->value, endDate: $dateTime);

			$calculatedData = $calculatedData->withReturnRates(
				twrPercentage: $twr,
				twrPercentagePerAnnum: CalculatorUtils::toPercentagePerAnnum($twr, $fromFirstTransactionDays),
				mwrPercentage: $mwr,
			);
		}

		$this->cache->save(key: $key, data: $calculatedData, user: $user, portfolio: $portfolio, date: $dateTime);

		return $calculatedData;
	}

	public function deletePortfolioData(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $date = null): void
	{
		$date = $date !== null ? DateTimeUtils::setEndOfDateTime($date) : null;

		$this->cache->clean(user: $user, portfolio: $portfolio, date: $date);
	}

	/**
	 * Fetch the portfolio value for a given date without computing TWR/MWR.
	 * Used internally by TwrCalculator to look up historical sub-period values.
	 */
	private function getPortfolioValue(User $user, Portfolio $portfolio, DateTimeImmutable $dateTime): Decimal
	{
		$dateTime = DateTimeUtils::setEndOfDateTime($dateTime);
		$key = $portfolio->id . '-' . $dateTime->getTimestamp();

		/** @var CalculatedDataDto|null $cached */
		$cached = $this->cache->load($key);
		if ($cached !== null) {
			return $cached->value;
		}

		$calculatedData = $this->computeBasicPortfolioData($user, $portfolio, $dateTime);

		$this->cache->save(key: $key, data: $calculatedData, user: $user, portfolio: $portfolio, date: $dateTime);

		return $calculatedData->value;
	}

	private function computeBasicPortfolioData(
		User $user,
		Portfolio $portfolio,
		DateTimeImmutable $dateTime,
		?DateTimeImmutable $firstTransactionDate = null,
	): CalculatedDataDto {
		$assetDatas = [];

		$assets = $this->assetProvider->getAssets(user: $user, portfolio: $portfolio, dateTime: $dateTime);
		foreach ($assets as $asset) {
			$assetData = $this->assetDataProvider->getAssetData($user, $portfolio, $asset, $dateTime);
			if ($assetData === null) {
				continue;
			}

			$assetDatas[] = $assetData;
		}

		$firstTransactionDate ??= $this->transactionProvider->getFirstTransaction($user, $portfolio)->actionCreated ?? $dateTime;

		return $this->dataCalculator->calculate($assetDatas, $dateTime, $firstTransactionDate);
	}

	/**
	 * Collect Buy/Sell transactions and aggregate them into daily cash flows (portfolio perspective).
	 *
	 * @return list<PortfolioCashFlowDto>
	 */
	private function buildCashFlows(User $user, Portfolio $portfolio, DateTimeImmutable $beforeDate): array
	{
		$transactions = $this->transactionProvider->getTransactions(
			user: $user,
			portfolio: $portfolio,
			actionCreatedBefore: $beforeDate,
			actionTypes: [TransactionActionTypeEnum::Buy, TransactionActionTypeEnum::Sell],
			orderBy: [TransactionOrderByEnum::ActionCreated->value => OrderDirectionEnum::ASC],
		);

		/** @var array<string, Decimal> $netByDate */
		$netByDate = [];
		/** @var array<string, DateTimeImmutable> $dateByKey */
		$dateByKey = [];

		foreach ($transactions as $transaction) {
			$key = $transaction->actionCreated->format('Y-m-d');
			if (!isset($netByDate[$key])) {
				$netByDate[$key] = new Decimal(0);
				$dateByKey[$key] = $transaction->actionCreated;
			}

			// units × priceDefaultCurrency: Buy = positive units, Sell = negative units.
			$netByDate[$key] = $netByDate[$key]->add($transaction->units->mul($transaction->priceDefaultCurrency));
		}

		$cashFlows = [];
		foreach ($netByDate as $key => $net) {
			$cashFlows[] = new PortfolioCashFlowDto(date: $dateByKey[$key], netCashFlow: $net);
		}

		return $cashFlows;
	}
}
