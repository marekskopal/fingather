<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Broker;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Enum\TransactionCreateTypeEnum;
use FinGather\Model\Entity\Portfolio;
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
		Portfolio $portfolio,
		?Asset $asset = null,
		?DateTimeImmutable $dateTime = null,
		?array $actionTypes = null,
		?int $limit = null,
		?int $offset = null,
	): array {
		return $this->transactionRepository->findTransactions(
			$user->getId(),
			$portfolio->getId(),
			$asset?->getId(),
			$dateTime,
			$actionTypes,
			$limit,
			$offset,
		);
	}

	/** @param list<TransactionActionTypeEnum> $actionTypes */
	public function countTransactions(
		User $user,
		?Portfolio $portfolio = null,
		?Asset $asset = null,
		?DateTimeImmutable $dateTime = null,
		?array $actionTypes = null,
	): int {
		return $this->transactionRepository->countTransactions(
			$user->getId(),
			$portfolio?->getId(),
			$asset?->getId(),
			$dateTime,
			$actionTypes,
		);
	}

	public function getTransaction(User $user, int $transactionId): ?Transaction
	{
		return $this->transactionRepository->findTransaction($transactionId, $user->getId());
	}

	public function getFirstTransaction(User $user, Portfolio $portfolio): ?Transaction
	{
		return $this->transactionRepository->findFirstTransaction($user->getId(), $portfolio->getId());
	}

	public function createTransaction(
		User $user,
		Portfolio $portfolio,
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
			portfolio: $portfolio,
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

	public function updateTransaction(
		Transaction $transaction,
		Asset $asset,
		Broker $broker,
		TransactionActionTypeEnum $actionType,
		DateTimeImmutable $actionCreated,
		Decimal $units,
		?Decimal $price,
		Currency $currency,
		?Decimal $tax,
		?string $notes,
		?string $importIdentifier,
	): Transaction {
		$modified = new DateTimeImmutable();

		$transaction->setAsset($asset);
		$transaction->setBroker($broker);
		$transaction->setActionType($actionType->value);
		$transaction->setActionCreated($actionCreated);
		$transaction->setModified($modified);
		$transaction->setUnits((string) $units);
		$transaction->setPrice($price !== null ? (string) $price : '0');
		$transaction->setCurrency($currency);
		$transaction->setTax($tax !== null ? (string) $tax : '0');
		$transaction->setNotes($notes);
		$transaction->setImportIdentifier($importIdentifier);

		$this->transactionRepository->persist($transaction);

		return $transaction;
	}

	public function deleteTransaction(Transaction $transaction): void
	{
		$this->transactionRepository->delete($transaction);
	}
}
