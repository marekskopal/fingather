<?php

declare(strict_types=1);

namespace FinGather\Service\DataCalculator;

use Decimal\Decimal;
use FinGather\Dto\Enum\RangeEnum;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Service\DataCalculator\Dto\DividendDataAssetDto;
use FinGather\Service\DataCalculator\Dto\DividendDataIntervalDto;
use FinGather\Service\Provider\TransactionProvider;
use FinGather\Utils\DateTimeUtils;
use Safe\DateTimeImmutable;

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

		$period = DateTimeUtils::getDatePeriod($range, $firstTransaction->getActionCreated());

		$actionCreatedAfter = $period->getStartDate();
		assert($actionCreatedAfter instanceof DateTimeImmutable);
		$actionCreatedBefore = $period->getEndDate();
		assert($actionCreatedBefore instanceof DateTimeImmutable);

		$transactions = $this->transactionProvider->getTransactions(
			user: $user,
			portfolio: $portfolio,
			actionTypes: [TransactionActionTypeEnum::Dividend],
			actionCreatedAfter: $actionCreatedAfter,
			actionCreatedBefore: $actionCreatedBefore,
		);

		$dividendData = [];

		foreach ($transactions as $transaction) {
			$dividendGain = $transaction->getPriceDefaultCurrency();

			$dateRangeKey = $this->getDateRangeKey($transaction->getActionCreated(), $range);

			$asset = $transaction->getAsset();

			$dividendDataAsset = $dividendData[$dateRangeKey][$asset->getId()] ?? new DividendDataAssetDto(
				id: $asset->getId(),
				tickerTicker: $asset->getTicker()->getTicker(),
				tickerName: $asset->getTicker()->getName(),
				dividendGain: new Decimal(0),
			);

			$dividendDataAsset->dividendGain = $dividendDataAsset->dividendGain->add($dividendGain);

			$dividendData[$dateRangeKey][$asset->getId()] = $dividendDataAsset;
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

	private function getDateRangeKey(\DateTimeImmutable $date, RangeEnum $range): string
	{
		return match ($range) {
			RangeEnum::SevenDays => $date->format('Y-m-d'),
			RangeEnum::OneMonth => $date->format('Y-m-d'),
			RangeEnum::ThreeMonths => $date->format('Y-m') . '-01',
			RangeEnum::SixMonths => $date->format('Y-m') . '-01',
			RangeEnum::YTD => $date->format('Y-m') . '-01',
			RangeEnum::OneYear => $date->format('Y-m') . '-01',
			RangeEnum::All => $date->format('Y-m') . '-01',
		};
	}
}
