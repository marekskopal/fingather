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

	public function createImport(User $user, Portfolio $portfolio, string $csvContent): Import
	{
		$import = new Import(
			user: $user,
			portfolio: $portfolio,
			created: new DateTimeImmutable(),
			csvContent: $csvContent,
		);
		$this->importRepository->persist($import);

		return $import;
	}

	public function deleteImport(Import $import): void
	{
		$this->importRepository->delete($import);
	}
}
