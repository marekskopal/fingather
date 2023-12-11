<?php

declare(strict_types=1);

namespace FinGather\Service\AlphaVantage;

use AlphaVantage\Client;
use AlphaVantage\Options;
use FinGather\Service\AlphaVantage\Dto\TickerSearchDto;

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
		foreach ($searchResults as $searchResult) {
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
					matchScore: $searchResult['9. matchScore'],
				);
			}
		}

		return null;
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
