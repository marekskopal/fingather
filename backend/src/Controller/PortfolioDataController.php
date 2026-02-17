<?php

declare(strict_types=1);

namespace FinGather\Controller;

use DateTimeImmutable;
use FinGather\Dto\Enum\RangeEnum;
use FinGather\Dto\PortfolioDataDto;
use FinGather\Dto\PortfolioDataWithBenchmarkDataDto;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Transaction;
use FinGather\Response\NotFoundResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\AssetProviderInterface;
use FinGather\Service\Provider\BenchmarkDataProvider;
use FinGather\Service\Provider\CurrentTransactionProviderInterface;
use FinGather\Service\Provider\PortfolioDataProvider;
use FinGather\Service\Provider\PortfolioProviderInterface;
use FinGather\Service\Provider\TickerProvider;
use FinGather\Service\Request\RequestService;
use FinGather\Utils\DateTimeUtils;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteGet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class PortfolioDataController
{
	public function __construct(
		private PortfolioDataProvider $portfolioDataProvider,
		private CurrentTransactionProviderInterface $currentTransactionProvider,
		private BenchmarkDataProvider $benchmarkDataProvider,
		private AssetProviderInterface $assetProvider,
		private PortfolioProviderInterface $portfolioProvider,
		private TickerProvider $tickerProvider,
		private RequestService $requestService,
	) {
	}

	#[RouteGet(Routes::PortfolioData->value)]
	public function actionGetPortfolioData(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		$dateTime = new DateTimeImmutable();

		$portfolioData = $this->portfolioDataProvider->getPortfolioData($user, $portfolio, $dateTime);

		return new JsonResponse(PortfolioDataDto::fromCalculatedDataDto($portfolioData));
	}

	#[RouteGet(Routes::PortfolioDataRange->value)]
	public function actionGetPortfolioDataRange(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		/**
		 * @var array{
		 *     range: value-of<RangeEnum>,
		 *     benchmarkAssetId?: string,
		 *     benchmarkTickerId?: string,
		 *     customRangeFrom?: string|null,
		 *     customRangeTo?: string|null,
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

		$range = RangeEnum::from($queryParams['range']);

		$benchmarkAssetId = ($queryParams['benchmarkAssetId'] ?? null) !== null ? (int) $queryParams['benchmarkAssetId'] : null;
		$benchmarkTickerId = ($queryParams['benchmarkTickerId'] ?? null) !== null ? (int) $queryParams['benchmarkTickerId'] : null;

		$benchmarkTicker = $benchmarkTickerId !== null ? $this->tickerProvider->getTicker($benchmarkTickerId) : null;
		if ($benchmarkTicker === null && $benchmarkAssetId !== null) {
			$benchmarkAsset = $this->assetProvider->getAsset($user, $benchmarkAssetId);
			$benchmarkTicker = $benchmarkAsset?->ticker;
		}

		$customRangeFrom = ($queryParams['customRangeFrom'] ?? null) !== null
			? new DateTimeImmutable($queryParams['customRangeFrom'])
			: null;
		$customRangeTo = ($queryParams['customRangeTo'] ?? null) !== null ? new DateTimeImmutable($queryParams['customRangeTo']) : null;

		$transactions = $this->currentTransactionProvider->getTransactions(
			user: $user,
			portfolio: $portfolio,
			actionTypes: [TransactionActionTypeEnum::Buy, TransactionActionTypeEnum::Sell],
		);
		usort($transactions, fn(Transaction $a, Transaction $b): int => $a->actionCreated <=> $b->actionCreated);

		if (count($transactions) === 0) {
			return new JsonResponse([]);
		}

		$firstTransaction = array_first($transactions);

		$portfolioDatas = [];

		$benchmarkDataFromDate = null;

		$datePeriod = DateTimeUtils::getDatePeriod(
			range: $range,
			customRangeFrom: $customRangeFrom,
			customRangeTo: $customRangeTo,
			firstDate: $firstTransaction->actionCreated,
			shiftStartDate: $range === RangeEnum::All,
		);
		foreach ($datePeriod as $dateTime) {
			/** @var DateTimeImmutable $dateTime */
			$portfolioData = $this->portfolioDataProvider->getPortfolioData($user, $portfolio, $dateTime);

			if ($benchmarkTicker === null) {
				$portfolioDatas[] = PortfolioDataWithBenchmarkDataDto::fromCalculatedDataDto($portfolioData);
				continue;
			}

			if ($benchmarkDataFromDate === null) {
				$benchmarkDataFromDate = $this->benchmarkDataProvider->getBenchmarkDataFromDate(
					user: $user,
					portfolio: $portfolio,
					benchmarkTicker: $benchmarkTicker,
					benchmarkFromDateTime: $datePeriod->getStartDate(),
					portfolioDataValue: $portfolioData->value,
				);

				$portfolioDatas[] = PortfolioDataWithBenchmarkDataDto::fromCalculatedDataDto($portfolioData, $benchmarkDataFromDate);
				continue;
			}

			$benchmarkData = $this->benchmarkDataProvider->getBenchmarkData(
				user: $user,
				portfolio: $portfolio,
				benchmarkTicker: $benchmarkTicker,
				transactions: $transactions,
				dateTime: $dateTime,
				benchmarkFromDateTime: $datePeriod->getStartDate(),
				benchmarkFromDateUnits: $benchmarkDataFromDate->units,
			);

			$portfolioDatas[] = PortfolioDataWithBenchmarkDataDto::fromCalculatedDataDto($portfolioData, $benchmarkData);
		}

		return new JsonResponse($portfolioDatas);
	}
}
