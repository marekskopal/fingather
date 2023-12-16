<?php

declare(strict_types=1);

namespace FinGather\Service\AlphaVantage;

use AlphaVantage\Api\TimeSeries;
use AlphaVantage\Client;
use AlphaVantage\Options;
use Brick\Math\BigDecimal;
use FinGather\Service\AlphaVantage\Dto\FxDailyDto;
use FinGather\Service\AlphaVantage\Dto\TickerSearchDto;
use FinGather\Service\AlphaVantage\Dto\TimeSerieDailyDto;
use Safe\DateTimeImmutable;

final class AlphaVantageApiClient
{
	private Client $client;

	public function __construct()
	{
		$option = new Options();
		$option->setApiKey((string) getenv('ALPHAVANTAGE_API_KEY'));

		$this->client = new Client($option);
	}

	public function tickerSearch(string $ticker): ?TickerSearchDto
	{
		$ticker = $this->sanitizeTicker($ticker);

		$searchResults = $this->client->timeSeries()->symbolSearch($ticker);
		foreach ($searchResults['bestMatches'] ?? [] as $searchResult) {
			if ($searchResult['1. symbol'] === $ticker) {
				return new TickerSearchDto(
					symbol: $searchResult['1. symbol'],
					name: $searchResult['2. name'],
					type: $searchResult['3. type'],
					region: $searchResult['4. region'],
					marketOpen: $searchResult['5. marketOpen'],
					marketClose: $searchResult['6. marketClose'],
					timezone: $searchResult['7. timezone'],
					currency: $searchResult['8. currency'],
					matchScore: (float) $searchResult['9. matchScore'],
				);
			}
		}

		return null;
	}

	/** @return list<TimeSerieDailyDto> */
	public function getTimeSeriesDaily(string $ticker): array
	{
		$ticker = $this->sanitizeTicker($ticker);

		$timeSeriesDaily = [];

		$results = $this->client->timeSeries()->dailyAdjusted($ticker, TimeSeries::OUTPUT_TYPE_FULL);
		foreach ($results['Time Series (Daily)'] as $date => $result) {
			$timeSeriesDaily[] = new TimeSerieDailyDto(
				date: new DateTimeImmutable($date),
				open: BigDecimal::of($result['1. open']),
				high: BigDecimal::of($result['2. high']),
				low: BigDecimal::of($result['3. low']),
				close: BigDecimal::of($result['4. close']),
				adjustedClose: BigDecimal::of($result['5. adjusted close']),
				volume: (int) $result['6. volume'],
				dividendAmount: BigDecimal::of($result['7. dividend amount']),
				splitCoefficient: $result['8. split coefficient'] !== null ? BigDecimal::of($result['8. split coefficient']) : BigDecimal::of(1.0),
			);
		}

		return $timeSeriesDaily;
	}

	/** @return list<FxDailyDto> */
	public function getFxDaily(string $toSymbol): array
	{
		$fxDaily = [];

		$results = $this->client->foreignExchange()->daily('USD', $toSymbol, TimeSeries::OUTPUT_TYPE_FULL);
		foreach ($results['Time Series FX (Daily)'] as $date => $result) {
			$fxDaily[] = new FxDailyDto(
				date: new DateTimeImmutable($date),
				open: BigDecimal::of($result['1. open']),
				high: BigDecimal::of($result['2. high']),
				low: BigDecimal::of($result['3. low']),
				close: BigDecimal::of($result['4. close']),
			);
		}

		return $fxDaily;
	}

	private function sanitizeTicker(string $ticker): string
	{
		$tickerParts = explode('.', $ticker);
		$marketMic = end($tickerParts);
		array_pop($tickerParts);
		$sanitizedTicker = implode('.', $tickerParts);

		switch ($marketMic) {
			case 'XLON':
				$sanitizedTicker .= '.LON';
				break;

			case 'XCNQ':
				$sanitizedTicker .= '.TRT';
				break;

			case 'XPAR':
				$sanitizedTicker .= '.PAR';
				break;

			case 'XFRA':
				$sanitizedTicker .= '.FRK';
				break;
		}

		return $sanitizedTicker;
	}
}
