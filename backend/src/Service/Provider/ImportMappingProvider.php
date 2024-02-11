<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Broker;
use FinGather\Model\Entity\ImportMapping;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\ImportMappingRepository;

class ImportMappingProvider
{
	public function __construct(private readonly ImportMappingRepository $importMappingRepository)
	{
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
}
