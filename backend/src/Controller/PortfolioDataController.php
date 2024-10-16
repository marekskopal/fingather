<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\Enum\RangeEnum;
use FinGather\Dto\PortfolioDataDto;
use FinGather\Dto\PortfolioDataWithBenchmarkDataDto;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Entity\Transaction;
use FinGather\Response\NotFoundResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\AssetProvider;
use FinGather\Service\Provider\BenchmarkDataProvider;
use FinGather\Service\Provider\CurrentTransactionProvider;
use FinGather\Service\Provider\PortfolioDataProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Request\RequestService;
use FinGather\Utils\DateTimeUtils;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteGet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Safe\DateTimeImmutable;

final class PortfolioDataController
{
	public function __construct(
		private readonly PortfolioDataProvider $portfolioDataProvider,
		private readonly CurrentTransactionProvider $currentTransactionProvider,
		private readonly BenchmarkDataProvider $benchmarkDataProvider,
		private readonly AssetProvider $assetProvider,
		private readonly PortfolioProvider $portfolioProvider,
		private readonly RequestService $requestService,
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
		$benchmarkAsset = $benchmarkAssetId !== null ? $this->assetProvider->getAsset($user, $benchmarkAssetId) : null;

		$customRangeFrom = ($queryParams['customRangeFrom'] ?? null) !== null
			? new DateTimeImmutable($queryParams['customRangeFrom'])
			: null;
		$customRangeTo = ($queryParams['customRangeTo'] ?? null) !== null ? new DateTimeImmutable($queryParams['customRangeTo']) : null;

		$transactions = $this->currentTransactionProvider->getTransactions(
			user: $user,
			portfolio: $portfolio,
			actionTypes: [TransactionActionTypeEnum::Buy, TransactionActionTypeEnum::Sell],
		);
		usort($transactions, fn(Transaction $a, Transaction $b): int => $a->getActionCreated() <=> $b->getActionCreated());

		if (count($transactions) === 0) {
			return new JsonResponse([]);
		}

		$firstTransaction = $transactions[array_key_first($transactions)];

		$portfolioDatas = [];

		$benchmarkDataFromDate = null;

		$datePeriod = DateTimeUtils::getDatePeriod(
			range: $range,
			customRangeFrom: $customRangeFrom?->getInnerDateTime(),
			customRangeTo: $customRangeTo?->getInnerDateTime(),
			firstDate: $firstTransaction->getActionCreated(),
			shiftStartDate: $range === RangeEnum::All,
		);
		foreach ($datePeriod as $dateTime) {
			/** @var \DateTimeImmutable $dateTime */
			$dateTimeConverted = DateTimeImmutable::createFromRegular($dateTime);

			$portfolioData = $this->portfolioDataProvider->getPortfolioData($user, $portfolio, $dateTimeConverted);

			if ($benchmarkAsset === null) {
				$portfolioDatas[] = PortfolioDataWithBenchmarkDataDto::fromCalculatedDataDto($portfolioData);
				continue;
			}

			if ($benchmarkDataFromDate === null) {
				$benchmarkDataFromDate = $this->benchmarkDataProvider->getBenchmarkDataFromDate(
					user: $user,
					portfolio: $portfolio,
					benchmarkAsset: $benchmarkAsset,
					benchmarkFromDateTime: $datePeriod->getStartDate(),
					portfolioDataValue: $portfolioData->value,
				);

				$portfolioDatas[] = PortfolioDataWithBenchmarkDataDto::fromCalculatedDataDto($portfolioData, $benchmarkDataFromDate);
			}

			$benchmarkData = $this->benchmarkDataProvider->getBenchmarkData(
				user: $user,
				portfolio: $portfolio,
				benchmarkAsset: $benchmarkAsset,
				transactions: $transactions,
				dateTime: $dateTimeConverted,
				benchmarkFromDateTime: $datePeriod->getStartDate(),
				benchmarkFromDateUnits: $benchmarkDataFromDate->units,
			);

			$portfolioDatas[] = PortfolioDataWithBenchmarkDataDto::fromCalculatedDataDto($portfolioData, $benchmarkData);
		}

		return new JsonResponse($portfolioDatas);
	}
}
