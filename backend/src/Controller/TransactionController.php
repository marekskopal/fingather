<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\TransactionDto;
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
	)
	{
	}

	public function actionGetTransactions(ServerRequestInterface $request): ResponseInterface
	{
		$queryParams = $request->getQueryParams();

		$user = $this->requestService->getUser($request);

		$assetId = $queryParams['assetId'] ?? null ? (int) $queryParams['assetId'] : null;
		$asset = $assetId !== null ?
			$this->assetProvider->getAsset($user, $assetId) :
			null;

		$transactions = $asset !== null ?
			$this->transactionProvider->getAssetTransactions($user, $asset) :
			$this->transactionProvider->getTransactions($user);

		$transactionDtos = array_map(
			fn (Transaction $transaction): TransactionDto => TransactionDto::fromEntity($transaction),
			$transactions
		);

		return new JsonResponse($transactionDtos);
	}
}
