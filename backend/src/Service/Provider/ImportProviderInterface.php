<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Import;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use Ramsey\Uuid\UuidInterface;

interface ImportProviderInterface
{
	public function getImportByUuid(User $user, UuidInterface $uuid): ?Import;

	public function createImport(User $user, Portfolio $portfolio, UuidInterface $uuid): Import;

	public function deleteImport(Import $import): void;
}
