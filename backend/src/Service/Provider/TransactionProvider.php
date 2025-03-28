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
use FinGather\Model\Repository\TransactionRepository;
use Iterator;

class TransactionProvider
{
	public function __construct(
		private readonly TransactionRepository $transactionRepository,
		private readonly ExchangeRateProvider $exchangeRateProvider,
	) {
	}

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
	): Iterator {
		return $this->transactionRepository->findTransactions(
			$user->id,
			$portfolio?->id,
			$asset?->id,
			$actionCreatedAfter,
			$actionCreatedBefore,
			$actionTypes,
			$created,
			$search,
			$limit,
			$offset,
			$orderBy,
		);
	}

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
	): int {
		return $this->transactionRepository->countTransactions(
			$user->id,
			$portfolio?->id,
			$asset?->id,
			$actionCreatedAfter,
			$actionCreatedBefore,
			$actionTypes,
			$created,
			$search,
		);
	}

	public function getTransaction(User $user, int $transactionId): ?Transaction
	{
		return $this->transactionRepository->findTransaction($transactionId, $user->id);
	}

	public function getFirstTransaction(User $user, Portfolio $portfolio, ?Asset $asset = null): ?Transaction
	{
		return $this->transactionRepository->findFirstTransaction($user->id, $portfolio->id, $asset?->id);
	}

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
	): Transaction {
		$created = new DateTimeImmutable();

		$tickerCurrency = $asset->ticker->currency;
		$defaultCurrency = $portfolio->currency;

		$price ??= new Decimal(0);
		$tax ??= new Decimal(0);
		$fee ??= new Decimal(0);

		$transaction = new Transaction(
			user: $user,
			portfolio: $portfolio,
			asset: $asset,
			brokerId: $broker?->id,
			actionType: $actionType,
			actionCreated: $actionCreated,
			createType: $createType,
			created: $created,
			modified: $created,
			units: $units,
			price: $price,
			priceTickerCurrency: $this->getPriceInCurrency($price, $currency, $tickerCurrency, $actionCreated),
			priceDefaultCurrency: $this->getPriceInCurrency($price, $currency, $defaultCurrency, $actionCreated),
			currency: $currency,
			tax: $tax,
			taxTickerCurrency: $this->getPriceInCurrency($tax, $taxCurrency, $tickerCurrency, $actionCreated),
			taxDefaultCurrency: $this->getPriceInCurrency($tax, $taxCurrency, $defaultCurrency, $actionCreated),
			taxCurrency: $taxCurrency,
			fee: $fee,
			feeTickerCurrency: $this->getPriceInCurrency($fee, $feeCurrency, $tickerCurrency, $actionCreated),
			feeDefaultCurrency: $this->getPriceInCurrency($fee, $feeCurrency, $defaultCurrency, $actionCreated),
			feeCurrency: $feeCurrency,
			notes: $notes,
			importIdentifier: $importIdentifier,
		);

		$this->transactionRepository->persist($transaction);

		return $transaction;
	}

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
	): Transaction {
		$modified = new DateTimeImmutable();

		$tickerCurrency = $asset->ticker->currency;
		$defaultCurrency = $transaction->portfolio->currency;

		$price ??= new Decimal(0);
		$tax ??= new Decimal(0);
		$fee ??= new Decimal(0);

		$transaction->asset = $asset;
		$transaction->brokerId = $broker?->id;
		$transaction->actionType = $actionType;
		$transaction->actionCreated = $actionCreated;
		$transaction->modified = $modified;
		$transaction->units = $units;
		$transaction->price = $price;
		$transaction->priceTickerCurrency = $this->getPriceInCurrency($price, $currency, $tickerCurrency, $actionCreated);
		$transaction->priceDefaultCurrency = $this->getPriceInCurrency($price, $currency, $defaultCurrency, $actionCreated);
		$transaction->currency = $currency;
		$transaction->tax = $tax;
		$transaction->taxTickerCurrency = $this->getPriceInCurrency($tax, $taxCurrency, $tickerCurrency, $actionCreated);
		$transaction->taxDefaultCurrency = $this->getPriceInCurrency($tax, $taxCurrency, $defaultCurrency, $actionCreated);
		$transaction->taxCurrency = $taxCurrency;
		$transaction->fee = $fee;
		$transaction->feeTickerCurrency = $this->getPriceInCurrency($fee, $feeCurrency, $tickerCurrency, $actionCreated);
		$transaction->feeDefaultCurrency = $this->getPriceInCurrency($fee, $feeCurrency, $defaultCurrency, $actionCreated);
		$transaction->feeCurrency = $feeCurrency;
		$transaction->notes = $notes;
		$transaction->importIdentifier = $importIdentifier;

		$this->transactionRepository->persist($transaction);

		return $transaction;
	}

	public function updateTransactionDefaultCurrency(Transaction $transaction): Transaction
	{
		$defaultCurrency = $transaction->portfolio->currency;
		$actionCreated = $transaction->actionCreated;

		$transaction->priceDefaultCurrency = $this->getPriceInCurrency(
			$transaction->price,
			$transaction->currency,
			$defaultCurrency,
			$actionCreated,
		);
		$transaction->taxDefaultCurrency = $this->getPriceInCurrency(
			$transaction->tax,
			$transaction->taxCurrency,
			$defaultCurrency,
			$actionCreated,
		);
		$transaction->feeDefaultCurrency = $this->getPriceInCurrency(
			$transaction->fee,
			$transaction->feeCurrency,
			$defaultCurrency,
			$actionCreated,
		);

		$this->transactionRepository->persist($transaction);

		return $transaction;
	}

	public function deleteTransaction(Transaction $transaction): void
	{
		$this->transactionRepository->delete($transaction);
	}

	private function getPriceInCurrency(Decimal $price, Currency $currencyFrom, Currency $currencyTo, DateTimeImmutable $created): Decimal
	{
		if ($currencyFrom->id === $currencyTo->id) {
			return $price;
		}

		if ($price->isZero()) {
			return $price;
		}

		$exchangeRate = $this->exchangeRateProvider->getExchangeRate($created, $currencyFrom, $currencyTo);
		return $price->mul($exchangeRate);
	}
}
