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
use Iterator;

class ImportMappingProvider
{
	public function __construct(
		private readonly ImportMappingRepository $importMappingRepository,
		private readonly ImportRepository $importRepository,
		private readonly TickerProvider $tickerProvider,
		private readonly BrokerProvider $brokerProvider,
	) {
	}

	/** @return Iterator<ImportMapping> */
	public function getPortfolioImportMappings(User $user, Portfolio $portfolio): Iterator
	{
		return $this->importMappingRepository->findByPortfolio($user->id, $portfolio->id);
	}

	public function getImportMapping(User $user, int $importMappingId): ?ImportMapping
	{
		$importMapping = $this->importMappingRepository->findById($importMappingId);
		if ($importMapping === null || $importMapping->user->id !== $user->id) {
			return null;
		}

		return $importMapping;
	}

	public function updateImportMapping(ImportMapping $importMapping, Ticker $ticker): ImportMapping
	{
		$importMapping->ticker = $ticker;
		$this->importMappingRepository->persist($importMapping);

		return $importMapping;
	}

	public function deleteImportMapping(ImportMapping $importMapping): void
	{
		$this->importMappingRepository->delete($importMapping);
	}

	/** @return array<string, ImportMapping> */
	public function getImportMappings(User $user, Portfolio $portfolio, Broker $broker): array
	{
		$importMappings = [];

		foreach ($this->importMappingRepository->findImportMappings($user->id, $portfolio->id, $broker->id) as $importMapping) {
			$importMappings[$broker->id . '-' . $importMapping->importTicker] = $importMapping;
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
		$import = $this->importRepository->findImportByUuid($importStart->uuid, $user->id);
		if ($import === null) {
			return;
		}

		foreach ($importStart->importMappings as $importStartImportMapping) {
			$ticker = $this->tickerProvider->getTicker($importStartImportMapping->tickerId);
			if ($ticker === null) {
				continue;
			}

			$broker = $this->brokerProvider->getBroker($user, $importStartImportMapping->brokerId);
			if ($broker === null) {
				continue;
			}

			$this->createImportMapping(
				user: $import->user,
				portfolio: $import->portfolio,
				broker: $broker,
				importTicker: $importStartImportMapping->importTicker,
				ticker: $ticker,
			);
		}
	}
}
