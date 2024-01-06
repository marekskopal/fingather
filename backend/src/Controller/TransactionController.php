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
use FinGather\Service\Provider\AssetProvider;
use FinGather\Service\Provider\BrokerProvider;
use FinGather\Service\Provider\CurrencyProvider;
use FinGather\Service\Provider\DataProvider;
use FinGather\Service\Provider\TransactionProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TransactionController
{
	public function __construct(
		private readonly TransactionProvider $transactionProvider,
		private readonly AssetProvider $assetProvider,
		private readonly BrokerProvider $brokerProvider,
		private readonly CurrencyProvider $currencyProvider,
		private readonly DataProvider $dataProvider,
		private readonly RequestService $requestService
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
			$transactions
		);

		return new JsonResponse(new TransactionListDto($transactionDtos, $count));
	}

	public function actionPostTransaction(ServerRequestInterface $request): ResponseInterface
	{
		$transactionCreate = TransactionCreateDto::fromJson($request->getBody()->getContents());

		$user = $this->requestService->getUser($request);

		$asset = $this->assetProvider->getAsset($user, $transactionCreate->assetId);
		if ($asset === null) {
			return new NotFoundResponse('Asset with id "' . $transactionCreate->assetId . '" was not found.');
		}

		$broker = $this->brokerProvider->getBroker($user, $transactionCreate->brokerId);
		if ($broker === null) {
			return new NotFoundResponse('Broker with id "' . $transactionCreate->brokerId . '" was not found.');
		}

		$currency = $this->currencyProvider->getCurrency($transactionCreate->currencyId);
		if ($currency === null) {
			return new NotFoundResponse('Currency with id "' . $transactionCreate->currencyId . '" was not found.');
		}

		$transaction = $this->transactionProvider->createTransaction(
			user: $user,
			asset: $asset,
			broker: $broker,
			actionType: $transactionCreate->actionType,
			actionCreated: $transactionCreate->actionCreated,
			createType: TransactionCreateTypeEnum::Manual,
			units: $transactionCreate->units,
			price: $transactionCreate->price,
			currency: $currency,
			tax: $transactionCreate->tax,
			notes: $transactionCreate->notes,
			importIdentifier: $transactionCreate->importIdentifier,
		);

		$this->dataProvider->deleteUserData($user, $transactionCreate->actionCreated);

		return new JsonResponse(TransactionDto::fromEntity($transaction));
	}
}
