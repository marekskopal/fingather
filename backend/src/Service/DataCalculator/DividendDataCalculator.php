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
use FinGather\Service\Provider\TransactionProvider;
use FinGather\Utils\DateTimeUtils;

final class DividendDataCalculator
{
	public function __construct(private readonly TransactionProvider $transactionProvider,)
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
			firstDate: $firstTransaction->getActionCreated(),
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
			$dividendYield = $transaction->getPriceDefaultCurrency();

			$dateRangeKey = $this->getDateRangeKey($transaction->getActionCreated(), $range);

			$asset = $transaction->getAsset();

			$dividendDataAsset = $dividendData[$dateRangeKey][$asset->id] ?? new DividendDataAssetDto(
				id: $asset->id,
				tickerTicker: $asset->getTicker()->getTicker(),
				tickerName: $asset->getTicker()->getName(),
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
			RangeEnum::SevenDays => $date->format('Y-m-d'),
			RangeEnum::OneMonth => $date->format('Y-m-d'),
			RangeEnum::ThreeMonths => $date->format('Y-m') . '-01',
			RangeEnum::SixMonths => $date->format('Y-m') . '-01',
			RangeEnum::YTD => $date->format('Y-m') . '-01',
			RangeEnum::OneYear => $date->format('Y-m') . '-01',
			RangeEnum::All => $date->format('Y-m') . '-01',
			RangeEnum::Custom => $date->format('Y-m') . '-01',
		};
	}
}
