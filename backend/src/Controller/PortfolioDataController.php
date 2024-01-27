<?php

declare(strict_types=1);

namespace FinGather\Controller;

use Decimal\Decimal;
use FinGather\Dto\Enum\PortfolioDataRangeEnum;
use FinGather\Dto\PortfolioDataDto;
use FinGather\Dto\PortfolioDataWithBenchmarkDataDto;
use FinGather\Service\Provider\AssetProvider;
use FinGather\Service\Provider\BenchmarkDataProvider;
use FinGather\Service\Provider\PortfolioDataProvider;
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
		private readonly RequestService $requestService,
	) {
	}

	/**
	 * @param array{string $portfolioId} $args
	 */
	public function actionGetPortfolioData(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		$dateTime = new DateTimeImmutable();

		$portfolioData = $this->portfolioDataProvider->getPortfolioData($user, $dateTime);

		return new JsonResponse(PortfolioDataDto::fromEntity($portfolioData));
	}

	public function actionGetPortfolioDataRange(ServerRequestInterface $request, array $args): ResponseInterface
	{
		/** @var array{range: value-of<PortfolioDataRangeEnum>, benchmarkAssetId?: string} $queryParams */
		$queryParams = $request->getQueryParams();

		$user = $this->requestService->getUser($request);

		$range = PortfolioDataRangeEnum::from($queryParams['range']);

		$benchmarkAssetId = ($queryParams['benchmarkAssetId'] ?? null) !== null ? (int) $queryParams['benchmarkAssetId'] : null;
		$benchmarkAsset = $benchmarkAssetId !== null ? $this->assetProvider->getAsset($user, $benchmarkAssetId) : null;

		$firstTransaction = $this->transactionProvider->getFirstTransaction($user);
		if ($firstTransaction === null) {
			return new JsonResponse([]);
		}

		$portfolioDatas = [];

		$firstDateTime = null;
		$benchmarkDataFromDate = null;

		foreach (DateTimeUtils::getDatePeriod($range, $firstTransaction->getActionCreated()) as $dateTime) {
			/** @var \DateTimeImmutable $dateTime */
			$dateTimeConverted = DateTimeImmutable::createFromRegular($dateTime);

			$portfolioData = $this->portfolioDataProvider->getPortfolioData($user, $dateTimeConverted);

			if ($benchmarkAsset === null) {
				$portfolioDatas[] = PortfolioDataWithBenchmarkDataDto::fromEntity($portfolioData);
				continue;
			}

			if ($firstDateTime === null || $benchmarkDataFromDate === null) {
				$firstDateTime = $dateTimeConverted;
				$benchmarkDataFromDate = $this->benchmarkDataProvider->getBenchmarkDataFromDate(
					user: $user,
					benchmarkAsset: $benchmarkAsset,
					benchmarkFromDateTime: $firstDateTime,
					portfolioDataValue: new Decimal($portfolioData->getValue()),
				);

				$portfolioDatas[] = PortfolioDataWithBenchmarkDataDto::fromEntity($portfolioData, $benchmarkDataFromDate);
			}

			$benchmarkData = $this->benchmarkDataProvider->getBenchmarkData(
				user: $user,
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
