<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use Decimal\Decimal;
use FinGather\Dto\Enum\RangeEnum;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Service\Provider\Dto\DividendDataAssetDto;
use FinGather\Service\Provider\Dto\DividendDataIntervalDto;
use FinGather\Utils\DateTimeUtils;
use Safe\DateTimeImmutable;

class DividendDataProvider
{
	public function __construct(
		private readonly TransactionProvider $transactionProvider,
		private readonly ExchangeRateProvider $exchangeRateProvider,
	) {
	}

	/** @return list<DividendDataIntervalDto> */
	public function getDividendData(User $user, Portfolio $portfolio, RangeEnum $range): array
	{
		$firstTransaction = $this->transactionProvider->getFirstTransaction($user, $portfolio);
		if ($firstTransaction === null) {
			return [];
		}

		$period = DateTimeUtils::getDatePeriod($range, $firstTransaction->getActionCreated());

		$defaultCurrency = $user->getDefaultCurrency();

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
			if ($transaction->getCurrency()->getId() === $defaultCurrency->getId()) {
				$dividendGain = $transaction->getPrice();
			} else {
				$dividendExchangeRate = $this->exchangeRateProvider->getExchangeRate(
					$transaction->getActionCreated(),
					$transaction->getCurrency(),
					$defaultCurrency,
				);

				$dividendGain = $transaction->getPrice()->mul($dividendExchangeRate);
			}

			$yearMonth = $transaction->getActionCreated()->format('Y-m');

			$asset = $transaction->getAsset();

			$dividendDataAsset = $dividendData[$yearMonth][$asset->getId()] ?? new DividendDataAssetDto(
				id: $asset->getId(),
				tickerTicker: $asset->getTicker()->getTicker(),
				tickerName: $asset->getTicker()->getName(),
				dividendGain: new Decimal(0),
			);

			$dividendDataAsset->dividendGain = $dividendDataAsset->dividendGain->add($dividendGain);

			$dividendData[$yearMonth][$asset->getId()] = $dividendDataAsset;
		}

		ksort($dividendData);

		$dividendDataIntervals = [];
		foreach ($dividendData as $yearMonth => $dividendDataAssets) {
			$dividendDataIntervals[] = new DividendDataIntervalDto(
				interval: $yearMonth,
				dividendDataAssets: array_values($dividendDataAssets),
			);
		}

		return $dividendDataIntervals;
	}
}
