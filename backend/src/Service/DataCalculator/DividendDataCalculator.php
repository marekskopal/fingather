<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Dto\Enum\RangeEnum;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\DividendDataAssetDto;
use FinGather\Service\DataCalculator\Dto\DividendDataIntervalDto;
use FinGather\Service\Provider\TransactionProviderInterface;
use FinGather\Utils\DateTimeUtils;

final readonly class DividendDataCalculator
{
	public function __construct(private TransactionProviderInterface $transactionProvider,)
	{
	}

	/** @return list<DividendDataIntervalDto> */
	public function getDividendData(User $user, Portfolio $portfolio, RangeEnum $range): array
	{
		$firstTransaction = $this->transactionProvider->getFirstTransaction($user, $portfolio);
		if ($firstTransaction === null) {
			return [];
		}

		$period = DateTimeUtils::getDatePeriod(
			range: $range,
			firstDate: $firstTransaction->actionCreated,
			shiftStartDate: $range === RangeEnum::All,
		);

		$actionCreatedAfter = $period->getStartDate();
		$actionCreatedBefore = $period->getEndDate();

		$transactions = $this->transactionProvider->getTransactions(
			user: $user,
			portfolio: $portfolio,
			actionTypes: [TransactionActionTypeEnum::Dividend],
			actionCreatedAfter: $actionCreatedAfter,
			actionCreatedBefore: $actionCreatedBefore,
		);

		$dividendData = [];

		foreach ($transactions as $transaction) {
			$dividendYield = $transaction->priceDefaultCurrency;

			$dateRangeKey = $this->getDateRangeKey($transaction->actionCreated, $range);

			$asset = $transaction->asset;

			$dividendDataAsset = $dividendData[$dateRangeKey][$asset->id] ?? new DividendDataAssetDto(
				id: $asset->id,
				tickerTicker: $asset->ticker->ticker,
				tickerName: $asset->ticker->name,
				dividendYield: new Decimal(0),
			);

			$dividendDataAsset->dividendYield = $dividendDataAsset->dividendYield->add($dividendYield);

			$dividendData[$dateRangeKey][$asset->id] = $dividendDataAsset;
		}

		ksort($dividendData);

		$dividendDataIntervals = [];
		foreach ($dividendData as $dateRangeKey => $dividendDataAssets) {
			$dividendDataIntervals[] = new DividendDataIntervalDto(
				interval: $dateRangeKey,
				dividendDataAssets: array_values($dividendDataAssets),
			);
		}

		return $dividendDataIntervals;
	}

	private function getDateRangeKey(DateTimeImmutable $date, RangeEnum $range): string
	{
		return match ($range) {
			RangeEnum::SevenDays, RangeEnum::OneMonth => $date->format('Y-m-d'),
			RangeEnum::ThreeMonths, RangeEnum::SixMonths, RangeEnum::YTD,
			RangeEnum::OneYear, RangeEnum::All, RangeEnum::Custom => $date->format('Y-m') . '-01',
		};
	}
}
