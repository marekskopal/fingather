<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\Enum\RangeEnum;
use FinGather\Dto\PortfolioDataDto;
use FinGather\Dto\PortfolioDataWithBenchmarkDataDto;
use FinGather\Model\Entity\Enum\TransactionActionTypeEnum;
use FinGather\Model\Repository\Enum\OrderDirectionEnum;
use FinGather\Response\NotFoundResponse;
use FinGather\Service\Provider\AssetProvider;
use FinGather\Service\Provider\BenchmarkDataProvider;
use FinGather\Service\Provider\PortfolioDataProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Provider\TransactionProvider;
use FinGather\Service\Request\RequestService;
use FinGather\Utils\DateTimeUtils;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Safe\DateTimeImmutable;

class PortfolioDataController
{
	public function __construct(
		private readonly PortfolioDataProvider $portfolioDataProvider,
		private readonly TransactionProvider $transactionProvider,
		private readonly BenchmarkDataProvider $benchmarkDataProvider,
		private readonly AssetProvider $assetProvider,
		private readonly PortfolioProvider $portfolioProvider,
		private readonly RequestService $requestService,
	) {
	}

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

		return new JsonResponse(PortfolioDataDto::fromEntity($portfolioData));
	}

	public function actionGetPortfolioDataRange(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		/** @var array{range: value-of<RangeEnum>, benchmarkAssetId?: string} $queryParams */
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

		$transactions = $this->transactionProvider->getTransactions(
			user: $user,
			portfolio: $portfolio,
			actionTypes: [TransactionActionTypeEnum::Buy, TransactionActionTypeEnum::Sell],
			orderDirection: OrderDirectionEnum::ASC,
		);

		$firstTransaction = $this->transactionProvider->getFirstTransaction($user, $portfolio);
		if ($firstTransaction === null) {
			return new JsonResponse([]);
		}

		$portfolioDatas = [];

		$firstDateTime = null;
		$benchmarkDataFromDate = null;

		foreach (DateTimeUtils::getDatePeriod($range, $firstTransaction->getActionCreated()) as $dateTime) {
			/** @var \DateTimeImmutable $dateTime */
			$dateTimeConverted = DateTimeImmutable::createFromRegular($dateTime);

			$portfolioData = $this->portfolioDataProvider->getPortfolioData($user, $portfolio, $dateTimeConverted);

			if ($benchmarkAsset === null) {
				$portfolioDatas[] = PortfolioDataWithBenchmarkDataDto::fromEntity($portfolioData);
				continue;
			}

			if ($firstDateTime === null || $benchmarkDataFromDate === null) {
				$firstDateTime = $dateTimeConverted;
				$benchmarkDataFromDate = $this->benchmarkDataProvider->getBenchmarkDataFromDate(
					user: $user,
					portfolio: $portfolio,
					benchmarkAsset: $benchmarkAsset,
					benchmarkFromDateTime: $firstDateTime,
					portfolioDataValue: $portfolioData->getValue(),
				);

				$portfolioDatas[] = PortfolioDataWithBenchmarkDataDto::fromEntity($portfolioData, $benchmarkDataFromDate);
			}

			$benchmarkData = $this->benchmarkDataProvider->getBenchmarkData(
				user: $user,
				portfolio: $portfolio,
				benchmarkAsset: $benchmarkAsset,
				transactions: $transactions,
				dateTime: $dateTimeConverted,
				benchmarkFromDateTime: $firstDateTime,
				benchmarkFromDateUnits: $benchmarkDataFromDate->getUnits(),
			);

			$portfolioDatas[] = PortfolioDataWithBenchmarkDataDto::fromEntity($portfolioData, $benchmarkData);
		}

		return new JsonResponse($portfolioDatas);
	}
}
