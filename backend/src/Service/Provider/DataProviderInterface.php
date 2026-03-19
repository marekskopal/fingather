<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;

interface DataProviderInterface
{
	public function deleteData(?User $user = null, ?Portfolio $portfolio = null, ?DateTimeImmutable $firstDate = null): void;

	public function deleteUserData(
		User $user,
		?Portfolio $portfolio = null,
		?DateTimeImmutable $firstDate = null,
		bool $recalculateTransactions = false,
	): void;
}
