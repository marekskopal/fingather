<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Dto\ImportStartDto;
use FinGather\Model\Entity\Broker;
use FinGather\Model\Entity\ImportMapping;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\ImportMappingRepository;
use FinGather\Model\Repository\ImportRepository;
use FinGather\Model\Repository\TickerRepository;

class ImportMappingProvider
{
	public function __construct(
		private readonly ImportMappingRepository $importMappingRepository,
		private readonly ImportRepository $importRepository,
		private readonly TickerRepository $tickerRepository,
	) {
	}

	/** @return array<string, ImportMapping> */
	public function getImportMappings(User $user, Portfolio $portfolio, Broker $broker): array
	{
		$importMappings = [];

		foreach ($this->importMappingRepository->findImportMappings(
			$user->getId(),
			$portfolio->getId(),
			$broker->getId(),
		) as $importMapping) {
			$importMappings[$importMapping->getImportTicker()] = $importMapping;
		}

		return $importMappings;
	}

	public function createImportMapping(
		User $user,
		Portfolio $portfolio,
		Broker $broker,
		string $importTicker,
		Ticker $ticker,
	): ImportMapping {
		$importMapping = new ImportMapping(
			user: $user,
			portfolio: $portfolio,
			broker: $broker,
			importTicker: $importTicker,
			ticker: $ticker,
		);
		$this->importMappingRepository->persist($importMapping);

		return $importMapping;
	}

	public function createImportMappingFromImportStart(User $user, ImportStartDto $importStart): void
	{
		$import = $this->importRepository->findImport($importStart->importId, $user->getId());
		if ($import === null) {
			return;
		}

		foreach ($importStart->importMappings as $importStartImportMapping) {
			$ticker = $this->tickerRepository->findTicker($importStartImportMapping->tickerId);
			if ($ticker === null) {
				continue;
			}

			$this->createImportMapping(
				user: $import->getUser(),
				portfolio: $import->getPortfolio(),
				broker: $import->getBroker(),
				importTicker: $importStartImportMapping->importTicker,
				ticker: $ticker,
			);
		}
	}
}
