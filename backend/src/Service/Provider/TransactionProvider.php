<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Broker;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Enum\TransactionCreateTypeEnum;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\TransactionRepository;
use Safe\DateTimeImmutable;

class TransactionProvider
{
	public function __construct(private readonly TransactionRepository $transactionRepository)
	{
	}

	/**
	 * @param list<TransactionActionTypeEnum> $actionTypes
	 * @return array<Transaction>
	 */
	public function getTransactions(
		User $user,
		?Asset $asset = null,
		?DateTimeImmutable $dateTime = null,
		?array $actionTypes = null,
		?int $limit = null,
		?int $offset = null,
	): array {
		return $this->transactionRepository->findTransactions($user->getId(), $asset?->getId(), $dateTime, $actionTypes, $limit, $offset);
	}

	/** @param list<TransactionActionTypeEnum> $actionTypes */
	public function countTransactions(
		User $user,
		?Asset $asset = null,
		?DateTimeImmutable $dateTime = null,
		?array $actionTypes = null,
	): int {
		return $this->transactionRepository->countTransactions($user->getId(), $asset?->getId(), $dateTime, $actionTypes);
	}

	public function getFirstTransaction(User $user): ?Transaction
	{
		return $this->transactionRepository->findFirstTransaction($user->getId());
	}

	public function createTransaction(
		User $user,
		Asset $asset,
		Broker $broker,
		TransactionActionTypeEnum $actionType,
		DateTimeImmutable $actionCreated,
		TransactionCreateTypeEnum $createType,
		Decimal $units,
		?Decimal $price,
		Currency $currency,
		?Decimal $tax,
		?string $notes,
		?string $importIdentifier,
	): Transaction {
		$created = new DateTimeImmutable();

		$transaction = new Transaction(
			user: $user,
			asset: $asset,
			broker: $broker,
			actionType: $actionType->value,
			actionCreated: $actionCreated,
			createType: $createType->value,
			created: $created,
			modified: $created,
			units: (string) $units,
			price: $price !== null ? (string) $price : '0',
			currency: $currency,
			tax: $tax !== null ? (string) $tax : '0',
			notes: $notes,
			importIdentifier: $importIdentifier,
		);

		$this->transactionRepository->persist($transaction);

		return $transaction;
	}
}
