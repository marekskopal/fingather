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
use FinGather\Service\Provider\AssetProvider;
use FinGather\Service\Provider\BrokerProvider;
use FinGather\Service\Provider\CurrencyProvider;
use FinGather\Service\Provider\DataProvider;
use FinGather\Service\Provider\TransactionProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Safe\DateTimeImmutable;

class TransactionController
{
	public function __construct(
		private readonly TransactionProvider $transactionProvider,
		private readonly AssetProvider $assetProvider,
		private readonly BrokerProvider $brokerProvider,
		private readonly CurrencyProvider $currencyProvider,
		private readonly DataProvider $dataProvider,
		private readonly RequestService $requestService,
	) {
	}

	public function actionGetTransactions(ServerRequestInterface $request): ResponseInterface
	{
		/** @var array{assetId?: string, limit?: string, offset?: string, actionTypes?: string} $queryParams */
		$queryParams = $request->getQueryParams();

		$user = $this->requestService->getUser($request);

		$assetId = ($queryParams['assetId'] ?? null) !== null ? (int) $queryParams['assetId'] : null;
		$asset = $assetId !== null ?
			$this->assetProvider->getAsset($user, $assetId) :
			null;

		$limit = ($queryParams['limit'] ?? null) !== null ? (int) $queryParams['limit'] : null;
		$offset = ($queryParams['offset'] ?? null) !== null ? (int) $queryParams['offset'] : null;

		$actionTypes = ($queryParams['actionTypes'] ?? null) !== null ?
			array_map(fn (string $item) => TransactionActionTypeEnum::from($item), explode('|', $queryParams['actionTypes'])) :
			null;

		$transactions = $this->transactionProvider->getTransactions($user, $asset, null, $actionTypes, $limit, $offset);
		$count = $this->transactionProvider->countTransactions($user, $asset, null, $actionTypes);

		$transactionDtos = array_map(
			fn (Transaction $transaction): TransactionDto => TransactionDto::fromEntity($transaction),
			$transactions,
		);

		return new JsonResponse(new TransactionListDto($transactionDtos, $count));
	}

	/** @param array{transactionId: string} $args */
	public function actionGetTransaction(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$transactionId = (int) $args['transactionId'];
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

	public function actionCreateTransaction(ServerRequestInterface $request): ResponseInterface
	{
		$transactionDto = TransactionCreateDto::fromJson($request->getBody()->getContents());

		$user = $this->requestService->getUser($request);

		$asset = $this->assetProvider->getAsset($user, $transactionDto->assetId);
		if ($asset === null) {
			return new NotFoundResponse('Asset with id "' . $transactionDto->assetId . '" was not found.');
		}

		$broker = $this->brokerProvider->getBroker($user, $transactionDto->brokerId);
		if ($broker === null) {
			return new NotFoundResponse('Broker with id "' . $transactionDto->brokerId . '" was not found.');
		}

		$currency = $this->currencyProvider->getCurrency($transactionDto->currencyId);
		if ($currency === null) {
			return new NotFoundResponse('Currency with id "' . $transactionDto->currencyId . '" was not found.');
		}

		$transaction = $this->transactionProvider->createTransaction(
			user: $user,
			asset: $asset,
			broker: $broker,
			actionType: $transactionDto->actionType,
			actionCreated: $transactionDto->actionCreated,
			createType: TransactionCreateTypeEnum::Manual,
			units: $transactionDto->units,
			price: $transactionDto->price,
			currency: $currency,
			tax: $transactionDto->tax,
			notes: $transactionDto->notes,
			importIdentifier: $transactionDto->importIdentifier,
		);

		$this->dataProvider->deleteUserData($user, $transactionDto->actionCreated);

		return new JsonResponse(TransactionDto::fromEntity($transaction));
	}

	/** @param array{transactionId: string} $args */
	public function actionUpdateTransaction(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$transactionId = (int) $args['transactionId'];
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

		$broker = $this->brokerProvider->getBroker($user, $transactionDto->brokerId);
		if ($broker === null) {
			return new NotFoundResponse('Broker with id "' . $transactionDto->brokerId . '" was not found.');
		}

		$currency = $this->currencyProvider->getCurrency($transactionDto->currencyId);
		if ($currency === null) {
			return new NotFoundResponse('Currency with id "' . $transactionDto->currencyId . '" was not found.');
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
			notes: $transactionDto->notes,
			importIdentifier: $transactionDto->importIdentifier,
		);

		$this->dataProvider->deleteUserData($user, $transactionDto->actionCreated);

		return new JsonResponse(TransactionDto::fromEntity($transaction));
	}

	/** @param array{transactionId: string} $args */
	public function actionDeleteTransaction(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$transactionId = (int) $args['transactionId'];
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
			DateTimeImmutable::createFromRegular($transaction->getActionCreated()),
		);

		return new OkResponse();
	}
}
