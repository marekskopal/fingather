<?php

declare(strict_types=1);

namespace FinGather\Service\Update;

use FinGather\Model\Repository\MarketRepository;
use FinGather\Model\Repository\TickerRepository;
use FinGather\Utils\BatchUtils;
use MarekSkopal\OpenFigi\Dto\FigiResult;
use MarekSkopal\OpenFigi\Dto\MappingJob;
use MarekSkopal\OpenFigi\Enum\IdTypeEnum;
use MarekSkopal\OpenFigi\OpenFigi;

final readonly class TickerIsinUpdater
{
	public function __construct(
		private TickerRepository $tickerRepository,
		private MarketRepository $marketRepository,
		private OpenFigi $openFigi,
	) {
	}

	/** @param list<string> $isins */
	public function updateTickerIsins(array $isins): void
	{
		$maxJobsPerRequest = $this->openFigi->getMaxJobsPerRequest();

		BatchUtils::batchCall($isins, $maxJobsPerRequest, function (array $batchIsins): void {
			$batchMappingResults = $this->getBatchMappingResults($batchIsins);

			$j = 0;
			foreach ($batchMappingResults as $mappingResults) {
				if ($mappingResults === null) {
					$j++;
					continue;
				}

				foreach ($mappingResults as $mappingResult) {
					if ($mappingResult->exchCode === null) {
						continue;
					}

					$market = $this->marketRepository->findMarketByExchangeCode($mappingResult->exchCode);
					if ($market === null) {
						continue;
					}

					$ticker = $this->tickerRepository->findTickerByTicker(
						str_replace('/', '.', $mappingResult->ticker),
						[$market->id],
					);
					if ($ticker === null) {
						continue;
					}

					$ticker->isin = $batchIsins[$j];
					$this->tickerRepository->persist($ticker);
				}

				$j++;
			}
		});
	}

	/**
	 * @param list<string> $batchIsins
	 * @return list<list<FigiResult>|null>
	 */
	private function getBatchMappingResults(array $batchIsins): array
	{
		return $this->openFigi->mapping(
			array_map(
				fn(string $isin): MappingJob => new MappingJob(
					idType: IdTypeEnum::Isin,
					idValue: $isin,
				),
				$batchIsins,
			),
		);
	}
}
