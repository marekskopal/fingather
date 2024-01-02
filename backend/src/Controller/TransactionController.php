<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\TransactionDto;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Transaction;
use FinGather\Service\Provider\AssetProvider;
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

		$transactions = $asset !== null ?
			$this->transactionProvider->getAssetTransactions($user, $asset, null, $actionTypes) :
			$this->transactionProvider->getTransactions($user, null, $actionTypes, $limit, $offset);

		$transactionDtos = array_map(
			fn (Transaction $transaction): TransactionDto => TransactionDto::fromEntity($transaction),
			$transactions
		);

		return new JsonResponse($transactionDtos);
	}
}
