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
	/** @var array<string, array<int, list<Transaction>>> */
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
		$portfolioKey = $user->id . '-' . ($portfolio->id ?? 0);

		if (count($this->transactions[$portfolioKey]) > 0) {
			return $this->transactions[$portfolioKey];
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
			if (!isset($this->transactions[$portfolioKey][$assetId])) {
				$this->transactions[$portfolioKey][$assetId] = [];
			}

			$this->transactions[$portfolioKey][$assetId][] = $transaction;
		}

		return $this->transactions[$portfolioKey];
	}

	public function clear(): void
	{
		$this->transactions = [];
	}
}
