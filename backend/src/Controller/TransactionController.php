<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\TransactionCreateDto;
use FinGather\Dto\TransactionDto;
use FinGather\Dto\TransactionListDto;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Enum\TransactionCreateTypeEnum;
use FinGather\Model\Entity\Transaction;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\AssetProvider;
use FinGather\Service\Provider\BrokerProvider;
use FinGather\Service\Provider\CurrencyProvider;
use FinGather\Service\Provider\DataProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Provider\TransactionProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteDelete;
use MarekSkopal\Router\Attribute\RouteGet;
use MarekSkopal\Router\Attribute\RoutePost;
use MarekSkopal\Router\Attribute\RoutePut;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Safe\DateTimeImmutable;

final class TransactionController
{
	public function __construct(
		private readonly TransactionProvider $transactionProvider,
		private readonly AssetProvider $assetProvider,
		private readonly BrokerProvider $brokerProvider,
		private readonly CurrencyProvider $currencyProvider,
		private readonly DataProvider $dataProvider,
		private readonly PortfolioProvider $portfolioProvider,
		private readonly RequestService $requestService,
	) {
	}

	#[RouteGet(Routes::Transactions->value)]
	public function actionGetTransactions(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		/** @var array{
		 *     assetId?: string,
		 *     limit?: string,
		 *     offset?: string,
		 *     actionTypes?: string,
		 *     created?: string,
		 *     search?: string,
		 * } $queryParams
		 */
		$queryParams = $request->getQueryParams();

		$user = $this->requestService->getUser($request);

		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		$assetId = ($queryParams['assetId'] ?? null) !== null ? (int) $queryParams['assetId'] : null;
		$asset = $assetId !== null ?
			$this->assetProvider->getAsset($user, $assetId) :
			null;

		$limit = ($queryParams['limit'] ?? null) !== null ? (int) $queryParams['limit'] : null;
		$offset = ($queryParams['offset'] ?? null) !== null ? (int) $queryParams['offset'] : null;

		$actionTypes = ($queryParams['actionTypes'] ?? null) !== null ?
			array_map(fn (string $item) => TransactionActionTypeEnum::from($item), explode('|', $queryParams['actionTypes'])) :
			null;

		$created = null;
		if (($queryParams['created'] ?? null) !== null) {
			try {
				$created = DateTimeImmutable::createFromFormat('Y-m-d', $queryParams['created']);
			} catch (\Throwable $e) {
				return new NotFoundResponse('Invalid date format. Use "Y-m-d" format.');
			}
		}

		$search = ($queryParams['search'] ?? null) !== null ? $queryParams['search'] : null;

		$transactions = $this->transactionProvider->getTransactions(
			user: $user,
			portfolio: $portfolio,
			asset: $asset,
			actionTypes: $actionTypes,
			created: $created,
			search: $search,
			limit: $limit,
			offset: $offset,
		);
		$count = $this->transactionProvider->countTransactions(
			user: $user,
			portfolio: $portfolio,
			asset: $asset,
			actionTypes: $actionTypes,
			created: $created,
			search: $search,
		);

		$transactionDtos = array_map(
			fn (Transaction $transaction): TransactionDto => TransactionDto::fromEntity($transaction),
			$transactions,
		);

		return new JsonResponse(new TransactionListDto($transactionDtos, $count));
	}

	#[RouteGet(Routes::Transaction->value)]
	public function actionGetTransaction(ServerRequestInterface $request, int $transactionId): ResponseInterface
	{
		if ($transactionId < 1) {
			return new NotFoundResponse('Transaction id is required.');
		}

		$transaction = $this->transactionProvider->getTransaction(
			user: $this->requestService->getUser($request),
			transactionId: $transactionId,
		);
		if ($transaction === null) {
			return new NotFoundResponse('Transaction with id "' . $transactionId . '" was not found.');
		}

		return new JsonResponse(TransactionDto::fromEntity($transaction));
	}

	#[RoutePost(Routes::Transactions->value)]
	public function actionCreateTransaction(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		$transactionDto = TransactionCreateDto::fromJson($request->getBody()->getContents());

		$user = $this->requestService->getUser($request);

		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		$asset = $this->assetProvider->getAsset($user, $transactionDto->assetId);
		if ($asset === null) {
			return new NotFoundResponse('Asset with id "' . $transactionDto->assetId . '" was not found.');
		}

		$broker = null;
		if ($transactionDto->brokerId !== null) {
			$broker = $this->brokerProvider->getBroker($user, $transactionDto->brokerId);
			if ($broker === null) {
				return new NotFoundResponse('Broker with id "' . $transactionDto->brokerId . '" was not found.');
			}
		}

		$currency = $this->currencyProvider->getCurrency($transactionDto->currencyId);
		if ($currency === null) {
			return new NotFoundResponse('Currency with id "' . $transactionDto->currencyId . '" was not found.');
		}

		$taxCurrency = $this->currencyProvider->getCurrency($transactionDto->taxCurrencyId);
		if ($taxCurrency === null) {
			return new NotFoundResponse('Currency with id "' . $transactionDto->taxCurrencyId . '" was not found.');
		}

		$feeCurrency = $this->currencyProvider->getCurrency($transactionDto->feeCurrencyId);
		if ($feeCurrency === null) {
			return new NotFoundResponse('Currency with id "' . $transactionDto->feeCurrencyId . '" was not found.');
		}

		$transaction = $this->transactionProvider->createTransaction(
			user: $user,
			portfolio: $portfolio,
			asset: $asset,
			broker: $broker,
			actionType: $transactionDto->actionType,
			actionCreated: $transactionDto->actionCreated,
			createType: TransactionCreateTypeEnum::Manual,
			units: $transactionDto->units,
			price: $transactionDto->price,
			currency: $currency,
			tax: $transactionDto->tax,
			taxCurrency: $taxCurrency,
			fee: $transactionDto->fee,
			feeCurrency: $feeCurrency,
			notes: $transactionDto->notes,
			importIdentifier: $transactionDto->importIdentifier,
		);

		$this->dataProvider->deleteUserData($user, $portfolio, $transactionDto->actionCreated);

		return new JsonResponse(TransactionDto::fromEntity($transaction));
	}

	#[RoutePut(Routes::Transaction->value)]
	public function actionUpdateTransaction(ServerRequestInterface $request, int $transactionId): ResponseInterface
	{
		if ($transactionId < 1) {
			return new NotFoundResponse('Transaction id is required.');
		}

		$transaction = $this->transactionProvider->getTransaction(
			user: $this->requestService->getUser($request),
			transactionId: $transactionId,
		);
		if ($transaction === null) {
			return new NotFoundResponse('Transaction with id "' . $transactionId . '" was not found.');
		}

		$transactionDto = TransactionCreateDto::fromJson($request->getBody()->getContents());

		$user = $this->requestService->getUser($request);

		$asset = $this->assetProvider->getAsset($user, $transactionDto->assetId);
		if ($asset === null) {
			return new NotFoundResponse('Asset with id "' . $transactionDto->assetId . '" was not found.');
		}

		$broker = null;
		if ($transactionDto->brokerId !== null) {
			$broker = $this->brokerProvider->getBroker($user, $transactionDto->brokerId);
			if ($broker === null) {
				return new NotFoundResponse('Broker with id "' . $transactionDto->brokerId . '" was not found.');
			}
		}

		$currency = $this->currencyProvider->getCurrency($transactionDto->currencyId);
		if ($currency === null) {
			return new NotFoundResponse('Currency with id "' . $transactionDto->currencyId . '" was not found.');
		}

		$taxCurrency = $this->currencyProvider->getCurrency($transactionDto->taxCurrencyId);
		if ($taxCurrency === null) {
			return new NotFoundResponse('Currency with id "' . $transactionDto->taxCurrencyId . '" was not found.');
		}

		$feeCurrency = $this->currencyProvider->getCurrency($transactionDto->feeCurrencyId);
		if ($feeCurrency === null) {
			return new NotFoundResponse('Currency with id "' . $transactionDto->feeCurrencyId . '" was not found.');
		}

		$transaction = $this->transactionProvider->updateTransaction(
			transaction: $transaction,
			asset: $asset,
			broker: $broker,
			actionType: $transactionDto->actionType,
			actionCreated: $transactionDto->actionCreated,
			units: $transactionDto->units,
			price: $transactionDto->price,
			currency: $currency,
			tax: $transactionDto->tax,
			taxCurrency: $taxCurrency,
			fee: $transactionDto->fee,
			feeCurrency: $feeCurrency,
			notes: $transactionDto->notes,
			importIdentifier: $transactionDto->importIdentifier,
		);

		$this->dataProvider->deleteUserData($user, $transaction->getPortfolio(), $transactionDto->actionCreated);

		return new JsonResponse(TransactionDto::fromEntity($transaction));
	}

	#[RouteDelete(Routes::Transaction->value)]
	public function actionDeleteTransaction(ServerRequestInterface $request, int $transactionId): ResponseInterface
	{
		if ($transactionId < 1) {
			return new NotFoundResponse('Transaction id is required.');
		}

		$transaction = $this->transactionProvider->getTransaction(
			user: $this->requestService->getUser($request),
			transactionId: $transactionId,
		);
		if ($transaction === null) {
			return new NotFoundResponse('Transaction with id "' . $transactionId . '" was not found.');
		}

		$this->transactionProvider->deleteTransaction($transaction);

		$this->dataProvider->deleteUserData(
			$transaction->getUser(),
			$transaction->getPortfolio(),
			DateTimeImmutable::createFromRegular($transaction->getActionCreated()),
		);

		return new OkResponse();
	}
}
