<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Broker;
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

	public function createImport(User $user, Portfolio $portfolio, Broker $broker, string $csvContent): Import
	{
		$import = new Import(
			user: $user,
			portfolio: $portfolio,
			broker: $broker,
			created: new DateTimeImmutable(),
			csvContent: $csvContent,
		);
		$this->importRepository->persist($import);

		return $import;
	}
}
