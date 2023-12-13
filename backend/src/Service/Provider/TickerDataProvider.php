<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\TickerData;
use FinGather\Model\Repository\TickerDataRepository;
use FinGather\Service\AlphaVantage\AlphaVantageApiClient;
use Safe\DateTime;

class TickerDataProvider
{
	public function __construct(private readonly TickerDataRepository $tickerDataRepository, private readonly AlphaVantageApiClient $alphaVantageApiClient)
	{
	}

	public function getLastTickerData(Ticker $ticker, DateTime $beforeDate): ?TickerData
	{
		$dayOfWeek = $beforeDate->format('w');

		if ($dayOfWeek === 6) {
			$beforeDate->sub(new \DateInterval('2 days'));
		} elseif ($dayOfWeek === 5) {
			$beforeDate->sub(new \DateInterval('1 day'));
		}

		$lastTickerData = $this->tickerDataRepository->findLastTickerData($ticker->getId(), $beforeDate);
		if ($lastTickerData !== null) {
			return $lastTickerData;
		}

		$this->createTickerData($ticker);

		return $this->tickerDataRepository->findLastTickerData($ticker->getId(), $beforeDate);
	}


	public function createTickerData(Ticker $ticker): void
	{
		$actualDate = new DateTime('today');

		$dayOfWeek = $actualDate->format('w');

		if ($dayOfWeek === 6) {
			$actualDate->sub(new \DateInterval('2 days'));
		} elseif ($dayOfWeek === 5) {
			$actualDate->sub(new \DateInterval('1 day'));
		}

		$firstDate = (new DateTime('today'))->sub(new \DateInterval('3 years'));

		$lastTickerData = $this->tickerDataRepository->findLastTickerData($ticker->getId())
		if ($lastTickerData !== null && ($actualDate->getTimestamp() - $lastTickerData->getDate()->getTimestamp() < 86400))	{
			return;
		}

		$fromDate = $firstDate;
		if ($lastTickerData !== null) {
			$fromDate = $lastTickerData->getDate();
		}

		$dailyTimeSeries = $this->alphaVantageApiClient->getDailyTimeSeries($ticker->getTicker());

		foreach ($dailyTimeSeries as $dailyTimeSerie) {

		}

		/**




		 *
		 * var previousAssetTickerData = lastAssetTickerData;
		 * foreach (var dataPoint in stockTimeSeries.DataPoints)
		 * {
		 * if (dataPoint.Time < fromDate)
		 * {
		 * continue;
		 * }
		 *
		 * var adjustedDataPoint = (StockAdjustedDataPoint) dataPoint;
		 *
		 * var performance = 0.0;
		 * if (previousAssetTickerData is not null)
		 * {
		 * performance = Convert.ToDouble(dataPoint.ClosingPrice / (previousAssetTickerData.Close / 100)) - 100;
		 * }
		 *
		 * var assetTickerData = new AssetTickerData()
		 * {
		 * AssetTickerId = assetTicker.Id,
		 * Date = dataPoint.Time,
		 * Open = dataPoint.OpeningPrice,
		 * Close = dataPoint.ClosingPrice,
		 * High = dataPoint.HighestPrice,
		 * Low = dataPoint.LowestPrice,
		 * Volume = dataPoint.Volume,
		 * Performance = Math.Round(performance, 8),
		 * };
		 *
		 * if (adjustedDataPoint.SplitCoefficient is not null && adjustedDataPoint.SplitCoefficient != 1)
		 * {
		 * var splitExists = _splitRepository
		 * .FindBy(s => s.AssetTickerId == assetTicker.Id && s.Date == dataPoint.Time).Count();
		 *
		 * if (splitExists == 0)
		 * {
		 * var split = new Split()
		 * {
		 * AssetTickerId = assetTicker.Id,
		 * Date = dataPoint.Time,
		 * Factor = adjustedDataPoint.SplitCoefficient ?? 1,
		 * };
		 *
		 * _splitRepository.Add(split);
		 * _splitRepository.Commit();
		 * }
		 * }
		 *
		 * _assetTickerDataRepository.Add(assetTickerData);
		 * _assetTickerDataRepository.Commit();
		 *
		 * previousAssetTickerData = assetTickerData;
		 * }
		 * }
		 */
	}
}