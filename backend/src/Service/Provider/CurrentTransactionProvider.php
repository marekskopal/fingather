<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use DateTimeImmutable;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Transaction;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\Enum\OrderDirectionEnum;
use FinGather\Model\Repository\Enum\TransactionOrderByEnum;

class CurrentTransactionProvider
{
	/** @var array<int, list<Transaction>> */
	private array $transactions = [];

	public function __construct(private readonly TransactionProvider $transactionProvider)
	{
	}

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
	): array {
		$loadedTransactions = $this->loadTransactions(user: $user, portfolio: $portfolio);

		$transactions = $asset !== null ? $loadedTransactions[$asset->id] ?? [] : array_merge(...array_values($loadedTransactions));

		if ($actionCreatedBefore !== null) {
			$transactions = array_values(array_filter(
				$transactions,
				fn(Transaction $transaction) => $transaction->actionCreated <= $actionCreatedBefore,
			));
		}

		if ($actionTypes !== null) {
			$transactions = array_values(array_filter(
				$transactions,
				fn(Transaction $transaction) => in_array($transaction->actionType, $actionTypes, true),
			));
		}

		return $transactions;
	}

	/** @return array<int, list<Transaction>> */
	public function loadTransactions(User $user, ?Portfolio $portfolio = null): array
	{
		if (count($this->transactions) > 0) {
			return $this->transactions;
		}

		$transactions = $this->transactionProvider->getTransactions(
			user: $user,
			portfolio: $portfolio,
			actionTypes: [
				TransactionActionTypeEnum::Buy,
				TransactionActionTypeEnum::Sell,
				TransactionActionTypeEnum::Dividend,
				TransactionActionTypeEnum::Tax,
				TransactionActionTypeEnum::Fee,
				TransactionActionTypeEnum::DividendTax,
			],
			orderBy: [
				TransactionOrderByEnum::BrokerId->value => OrderDirectionEnum::ASC,
				TransactionOrderByEnum::ActionCreated->value => OrderDirectionEnum::ASC,
			],
		);

		$this->clear();

		foreach ($transactions as $transaction) {
			$assetId = $transaction->asset->id;
			if (!isset($this->transactions[$assetId])) {
				$this->transactions[$assetId] = [];
			}

			$this->transactions[$assetId][] = $transaction;
		}

		return $this->transactions;
	}

	public function clear(): void
	{
		$this->transactions = [];
	}
}
