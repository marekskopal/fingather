<?php

declare(strict_types=1);

namespace FinGather\Mcp\Tool;

use DateTimeImmutable;
use Decimal\Decimal;
use FinGather\Mcp\Dto\McpTransactionCreatedDto;
use FinGather\Mcp\Dto\McpTransactionDto;
use FinGather\Mcp\Dto\McpTransactionListDto;
use FinGather\Mcp\McpUserContextInterface;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Enum\TransactionCreateTypeEnum;
use FinGather\Service\Provider\AssetProviderInterface;
use FinGather\Service\Provider\CurrencyProviderInterface;
use FinGather\Service\Provider\DataProviderInterface;
use FinGather\Service\Provider\PortfolioProviderInterface;
use FinGather\Service\Provider\TransactionProviderInterface;
use Mcp\Capability\Attribute\McpTool;
use RuntimeException;

final readonly class TransactionTools
{
	public function __construct(
		private McpUserContextInterface $userContext,
		private PortfolioProviderInterface $portfolioProvider,
		private AssetProviderInterface $assetProvider,
		private TransactionProviderInterface $transactionProvider,
		private CurrencyProviderInterface $currencyProvider,
		private DataProviderInterface $dataProvider,
	) {
	}

	/**
	 * List transactions in a portfolio with optional filters.
	 * Returns newest transactions first.
	 *
	 * @param int $portfolioId Portfolio ID
	 * @param int $limit Maximum number of results (default 50, max 200)
	 * @param int $offset Pagination offset (default 0)
	 * @param string|null $search Search text (matches ticker symbol or notes)
	 * @param string|null $actionType Filter by type: Buy, Sell, Dividend, Tax, Fee, DividendTax
	 * @param string|null $dateFrom Filter transactions on or after this date (YYYY-MM-DD)
	 * @param string|null $dateTo Filter transactions on or before this date (YYYY-MM-DD)
	 */
	#[McpTool(name: 'list_transactions', description: 'List transactions with optional filters for type, date range, and search text')]
	public function listTransactions(
		int $portfolioId,
		int $limit = 50,
		int $offset = 0,
		?string $search = null,
		?string $actionType = null,
		?string $dateFrom = null,
		?string $dateTo = null,
	): McpTransactionListDto {
		$user = $this->userContext->getUser();

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			throw new RuntimeException(sprintf('Portfolio %d not found.', $portfolioId));
		}

		$limit = min($limit, 200);

		$actionTypes = null;
		if ($actionType !== null) {
			$enum = TransactionActionTypeEnum::tryFrom($actionType);
			if ($enum === null) {
				throw new RuntimeException(sprintf(
					'Invalid actionType "%s". Valid values: %s',
					$actionType,
					implode(', ', array_column(TransactionActionTypeEnum::cases(), 'value')),
				));
			}
			$actionTypes = [$enum];
		}

		$transactions = $this->transactionProvider->getTransactions(
			user: $user,
			portfolio: $portfolio,
			actionCreatedAfter: $dateFrom !== null ? new DateTimeImmutable($dateFrom) : null,
			actionCreatedBefore: $dateTo !== null ? new DateTimeImmutable($dateTo . ' 23:59:59') : null,
			actionTypes: $actionTypes,
			search: $search,
			limit: $limit,
			offset: $offset,
		);

		$items = [];
		foreach ($transactions as $transaction) {
			$items[] = McpTransactionDto::fromEntity($transaction);
		}

		return new McpTransactionListDto($items);
	}

	/**
	 * Add a new transaction to a portfolio.
	 * Use list_assets to find the assetId. Use search_tickers to find ticker information.
	 * Supported action types: Buy, Sell, Dividend, Tax, Fee, DividendTax.
	 * Currency must be a valid ISO 4217 code (e.g. USD, EUR, GBP).
	 *
	 * @param int $portfolioId Portfolio ID to add the transaction to
	 * @param int $assetId Asset ID (use list_assets to find existing assets)
	 * @param string $actionType Transaction type: Buy, Sell, Dividend, Tax, Fee, DividendTax
	 * @param string $units Number of units (use negative for sells if needed, but prefer "Sell" type)
	 * @param string $price Price per unit in the given currency
	 * @param string $currency ISO 4217 currency code for the price (e.g. USD)
	 * @param string $date Transaction date (YYYY-MM-DD)
	 * @param string $fee Brokerage fee (default "0")
	 * @param string|null $feeCurrency ISO 4217 currency code for the fee (defaults to transaction currency)
	 * @param string $tax Tax paid (default "0")
	 * @param string|null $taxCurrency ISO 4217 currency code for the tax (defaults to transaction currency)
	 * @param string|null $notes Optional notes
	 */
	#[McpTool(name: 'add_transaction', description: 'Record a new buy, sell, dividend, or fee transaction')]
	public function addTransaction(
		int $portfolioId,
		int $assetId,
		string $actionType,
		string $units,
		string $price,
		string $currency,
		string $date,
		string $fee = '0',
		?string $feeCurrency = null,
		string $tax = '0',
		?string $taxCurrency = null,
		?string $notes = null,
	): McpTransactionCreatedDto {
		$user = $this->userContext->getUser();

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			throw new RuntimeException(sprintf('Portfolio %d not found.', $portfolioId));
		}

		$asset = $this->assetProvider->getAsset($user, $assetId);
		if ($asset === null) {
			throw new RuntimeException(sprintf('Asset %d not found.', $assetId));
		}

		$actionTypeEnum = TransactionActionTypeEnum::tryFrom($actionType);
		if ($actionTypeEnum === null) {
			throw new RuntimeException(sprintf(
				'Invalid actionType "%s". Valid values: %s',
				$actionType,
				implode(', ', array_column(TransactionActionTypeEnum::cases(), 'value')),
			));
		}

		$currencyEntity = $this->currencyProvider->getCurrencyByCode($currency);
		if ($currencyEntity === null) {
			throw new RuntimeException(sprintf('Currency "%s" not found.', $currency));
		}

		$feeCurrencyCode = $feeCurrency ?? $currency;
		$feeCurrencyEntity = $this->currencyProvider->getCurrencyByCode($feeCurrencyCode);
		if ($feeCurrencyEntity === null) {
			throw new RuntimeException(sprintf('Fee currency "%s" not found.', $feeCurrencyCode));
		}

		$taxCurrencyCode = $taxCurrency ?? $currency;
		$taxCurrencyEntity = $this->currencyProvider->getCurrencyByCode($taxCurrencyCode);
		if ($taxCurrencyEntity === null) {
			throw new RuntimeException(sprintf('Tax currency "%s" not found.', $taxCurrencyCode));
		}

		$transaction = $this->transactionProvider->createTransaction(
			user: $user,
			portfolio: $portfolio,
			asset: $asset,
			broker: null,
			actionType: $actionTypeEnum,
			actionCreated: new DateTimeImmutable($date),
			createType: TransactionCreateTypeEnum::Manual,
			units: new Decimal($units),
			price: new Decimal($price),
			currency: $currencyEntity,
			tax: new Decimal($tax),
			taxCurrency: $taxCurrencyEntity,
			fee: new Decimal($fee),
			feeCurrency: $feeCurrencyEntity,
			notes: $notes,
			importIdentifier: null,
		);

		$this->dataProvider->deleteUserData(user: $user, portfolio: $portfolio);

		return McpTransactionCreatedDto::fromEntity($transaction);
	}
}
