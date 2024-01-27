<?php

declare(strict_types=1);

namespace FinGather\Controller;

use Decimal\Decimal;
use FinGather\Dto\Enum\PortfolioDataRangeEnum;
use FinGather\Dto\PortfolioDataDto;
use FinGather\Dto\PortfolioDataWithBenchmarkDataDto;
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
		/** @var array{range: value-of<PortfolioDataRangeEnum>, benchmarkAssetId?: string} $queryParams */
		$queryParams = $request->getQueryParams();

		$user = $this->requestService->getUser($request);

		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		$range = PortfolioDataRangeEnum::from($queryParams['range']);

		$benchmarkAssetId = ($queryParams['benchmarkAssetId'] ?? null) !== null ? (int) $queryParams['benchmarkAssetId'] : null;
		$benchmarkAsset = $benchmarkAssetId !== null ? $this->assetProvider->getAsset($user, $benchmarkAssetId) : null;

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
					portfolioDataValue: new Decimal($portfolioData->getValue()),
				);

				$portfolioDatas[] = PortfolioDataWithBenchmarkDataDto::fromEntity($portfolioData, $benchmarkDataFromDate);
			}

			$benchmarkData = $this->benchmarkDataProvider->getBenchmarkData(
				user: $user,
				portfolio: $portfolio,
				benchmarkAsset: $benchmarkAsset,
				dateTime: $dateTimeConverted,
				benchmarkFromDateTime: $firstDateTime,
				benchmarkFromDateUnits: new Decimal($benchmarkDataFromDate->getUnits()),
			);

			$portfolioDatas[] = PortfolioDataWithBenchmarkDataDto::fromEntity($portfolioData, $benchmarkData);
		}

		return new JsonResponse($portfolioDatas);
	}
}
