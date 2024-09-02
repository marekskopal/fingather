<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Import;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\ImportRepository;
use Safe\DateTimeImmutable;

class ImportProvider
{
	public function __construct(private readonly ImportRepository $importRepository)
	{
	}

	public function getImport(User $user, int $importId): ?Import
	{
		return $this->importRepository->findImport($importId, $user->getId());
	}

	public function createImport(User $user, Portfolio $portfolio): Import
	{
		$import = new Import(
			user: $user,
			portfolio: $portfolio,
			created: new DateTimeImmutable(),
		);
		$this->importRepository->persist($import);

		return $import;
	}

	public function deleteImport(Import $import): void
	{
		// Temporary disabled delete of import for debugging purposes
		//$this->importRepository->delete($import);
	}
}
