<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;

interface CurrentTransactionProviderInterface
{
	/**
	 * @param list<TransactionActionTypeEnum>|null $actionTypes
	 * @return list<Transaction>
	 */
	public function getTransactions(
		User $user,
		?Portfolio $portfolio = null,
		?Asset $asset = null,
		?DateTimeImmutable $actionCreatedBefore = null,
		?array $actionTypes = null,
	): array;

	/** @return array<int, list<Transaction>> */
	public function loadTransactions(User $user, ?Portfolio $portfolio = null): array;

	public function clear(?string $portfolioKey = null): void;
}
