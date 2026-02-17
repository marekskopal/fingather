<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Broker;
use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Enum\TransactionCreateTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\Enum\OrderDirectionEnum;
use FinGather\Model\Repository\Enum\TransactionOrderByEnum;
use Iterator;

interface TransactionProviderInterface
{
	/**
	 * @param list<TransactionActionTypeEnum>|null $actionTypes
	 * @param array<value-of<TransactionOrderByEnum>,OrderDirectionEnum> $orderBy
	 * @return Iterator<Transaction>
	 */
	public function getTransactions(
		User $user,
		?Portfolio $portfolio = null,
		?Asset $asset = null,
		?DateTimeImmutable $actionCreatedAfter = null,
		?DateTimeImmutable $actionCreatedBefore = null,
		?array $actionTypes = null,
		?DateTimeImmutable $created = null,
		?string $search = null,
		?int $limit = null,
		?int $offset = null,
		array $orderBy = [
			TransactionOrderByEnum::ActionCreated->value => OrderDirectionEnum::DESC,
		],
	): Iterator;

	/** @param list<TransactionActionTypeEnum>|null $actionTypes */
	public function countTransactions(
		User $user,
		?Portfolio $portfolio = null,
		?Asset $asset = null,
		?DateTimeImmutable $actionCreatedAfter = null,
		?DateTimeImmutable $actionCreatedBefore = null,
		?array $actionTypes = null,
		?DateTimeImmutable $created = null,
		?string $search = null,
	): int;

	public function getTransaction(User $user, int $transactionId): ?Transaction;

	public function getFirstTransaction(User $user, Portfolio $portfolio, ?Asset $asset = null): ?Transaction;

	public function createTransaction(
		User $user,
		Portfolio $portfolio,
		Asset $asset,
		?Broker $broker,
		TransactionActionTypeEnum $actionType,
		DateTimeImmutable $actionCreated,
		TransactionCreateTypeEnum $createType,
		Decimal $units,
		?Decimal $price,
		Currency $currency,
		?Decimal $tax,
		Currency $taxCurrency,
		?Decimal $fee,
		Currency $feeCurrency,
		?string $notes,
		?string $importIdentifier,
	): Transaction;

	public function updateTransaction(
		Transaction $transaction,
		Asset $asset,
		?Broker $broker,
		TransactionActionTypeEnum $actionType,
		DateTimeImmutable $actionCreated,
		Decimal $units,
		?Decimal $price,
		Currency $currency,
		?Decimal $tax,
		Currency $taxCurrency,
		?Decimal $fee,
		Currency $feeCurrency,
		?string $notes,
		?string $importIdentifier,
	): Transaction;

	public function updateTransactionDefaultCurrency(Transaction $transaction): Transaction;

	public function deleteTransaction(Transaction $transaction): void;
}
