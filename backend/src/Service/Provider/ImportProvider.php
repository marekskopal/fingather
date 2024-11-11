<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Import;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\ImportRepository;
use Ramsey\Uuid\UuidInterface;

class ImportProvider
{
	public function __construct(private readonly ImportRepository $importRepository)
	{
	}

	public function getImportByUuid(User $user, UuidInterface $uuid): ?Import
	{
		return $this->importRepository->findImportByUuid($uuid, $user->getId());
	}

	public function createImport(User $user, Portfolio $portfolio, UuidInterface $uuid): Import
	{
		$import = new Import(
			user: $user,
			portfolio: $portfolio,
			created: new DateTimeImmutable(),
			uuid: $uuid,
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
