<?php

declare(strict_types=1);

namespace FinGather\Controller;

use DateTimeImmutable;
use FinGather\Dto\AssetDataDto;
use FinGather\Dto\Enum\RangeEnum;
use FinGather\Response\NotFoundResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\AssetDataProvider;
use FinGather\Service\Provider\AssetProvider;
use FinGather\Service\Provider\TransactionProvider;
use FinGather\Service\Request\RequestServiceInterface;
use FinGather\Utils\DateTimeUtils;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteGet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AssetDataController
{
	public function __construct(
		private readonly AssetProvider $assetProvider,
		private readonly AssetDataProvider $assetDataProvider,
		private readonly TransactionProvider $transactionProvider,
		private readonly RequestServiceInterface $requestService,
	) {
	}

	#[RouteGet(Routes::AssetDataRange->value)]
	public function actionGetAssetDataRange(ServerRequestInterface $request, int $assetId): ResponseInterface
	{
		/** @var array{range?: value-of<RangeEnum>} $queryParams */
		$queryParams = $request->getQueryParams();

		$user = $this->requestService->getUser($request);

		if ($assetId < 1) {
			return new NotFoundResponse('Asset id is required.');
		}

		$asset = $this->assetProvider->getAsset($user, $assetId);
		if ($asset === null) {
			return new NotFoundResponse('Asset with id "' . $assetId . '" was not found.');
		}

		$portfolio = $asset->getPortfolio();

		if (!isset($queryParams['range'])) {
			return new NotFoundResponse('Range is required.');
		}

		$range = RangeEnum::from($queryParams['range']);

		$firstTransaction = $this->transactionProvider->getFirstTransaction($user, $portfolio, $asset);
		if ($firstTransaction === null) {
			return new JsonResponse([]);
		}

		$assetDatas = [];

		$datePeriod = DateTimeUtils::getDatePeriod(
			range: $range,
			firstDate: $firstTransaction->getActionCreated(),
			shiftStartDate: $range === RangeEnum::All,
		);
		foreach ($datePeriod as $dateTime) {
			/** @var DateTimeImmutable $dateTime */
			$assetData = $this->assetDataProvider->getAssetData(user: $user, portfolio: $portfolio, asset: $asset, dateTime: $dateTime);

			if ($assetData === null) {
				$assetDatas[] = AssetDataDto::fromNull($dateTime);
				continue;
			}

			$assetDatas[] = AssetDataDto::fromEntity($assetData);
		}

		return new JsonResponse($assetDatas);
	}
}
