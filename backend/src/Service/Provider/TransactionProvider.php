<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\TransactionRepository;
use Safe\DateTimeImmutable;

class TransactionProvider
{
	public function __construct(private readonly TransactionRepository $transactionRepository)
	{
	}

	/** @return array<Transaction> */
	public function getTransactions(User $user, ?DateTimeImmutable $dateTime = null): array
	{
		return $this->transactionRepository->findTransactions($user->getId(), $dateTime);
	}

	/** @return array<Transaction> */
	public function getAssetTransactions(User $user, Asset $asset, ?DateTimeImmutable $dateTime = null): array
	{
		return $this->transactionRepository->findAssetTransactions($asset->getId(), $asset->getId(), $dateTime);
	}
}
