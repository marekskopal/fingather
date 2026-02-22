<?php

declare(strict_types=1);

namespace FinGather\Controller;

use DateTimeImmutable;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Response\FileResponse;
use FinGather\Response\NotFoundResponse;
use FinGather\Route\Routes;
use FinGather\Service\Export\TransactionCsvExporter;
use FinGather\Service\Export\TransactionExcelExporter;
use FinGather\Service\Provider\AssetProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Provider\TransactionProvider;
use FinGather\Service\Request\RequestService;
use MarekSkopal\Router\Attribute\RouteGet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class TransactionExportController
{
	public function __construct(
		private TransactionProvider $transactionProvider,
		private TransactionCsvExporter $transactionCsvExporter,
		private TransactionExcelExporter $transactionExcelExporter,
		private AssetProvider $assetProvider,
		private PortfolioProvider $portfolioProvider,
		private RequestService $requestService,
	) {
	}

	#[RouteGet(Routes::TransactionExportCsv->value)]
	public function actionExportCsv(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		/** @var array{
		 *     assetId?: string,
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
		$asset = $assetId !== null ? $this->assetProvider->getAsset($user, $assetId) : null;

		$actionTypes = ($queryParams['actionTypes'] ?? null) !== null ?
			array_map(fn (string $item) => TransactionActionTypeEnum::from($item), explode('|', $queryParams['actionTypes'])) :
			null;

		$created = null;
		if (($queryParams['created'] ?? null) !== null) {
			$created = DateTimeImmutable::createFromFormat('Y-m-d', $queryParams['created']);
			if ($created === false) {
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
		);

		$csvPath = $this->transactionCsvExporter->export($transactions);

		try {
			return new FileResponse($csvPath, 'transactions.csv', 'text/csv');
		} finally {
			@unlink($csvPath);
		}
	}

	#[RouteGet(Routes::TransactionExportXlsx->value)]
	public function actionExportXlsx(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		/** @var array{
		 *     assetId?: string,
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
		$asset = $assetId !== null ? $this->assetProvider->getAsset($user, $assetId) : null;

		$actionTypes = ($queryParams['actionTypes'] ?? null) !== null ?
			array_map(fn (string $item) => TransactionActionTypeEnum::from($item), explode('|', $queryParams['actionTypes'])) :
			null;

		$created = null;
		if (($queryParams['created'] ?? null) !== null) {
			$created = DateTimeImmutable::createFromFormat('Y-m-d', $queryParams['created']);
			if ($created === false) {
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
		);

		$xlsxPath = $this->transactionExcelExporter->export($transactions);

		try {
			return new FileResponse($xlsxPath, 'transactions.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		} finally {
			@unlink($xlsxPath);
		}
	}
}
