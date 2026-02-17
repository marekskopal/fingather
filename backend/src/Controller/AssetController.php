<?php

declare(strict_types=1);

namespace FinGather\Controller;

use DateTimeImmutable;
use FinGather\Dto\AssetCreateDto;
use FinGather\Dto\AssetDto;
use FinGather\Dto\AssetWithPropertiesDto;
use FinGather\Dto\Enum\AssetOrderEnum;
use FinGather\Response\NotFoundResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\AssetDataProviderInterface;
use FinGather\Service\Provider\AssetProviderInterface;
use FinGather\Service\Provider\AssetWithPropertiesProvider;
use FinGather\Service\Provider\GroupProvider;
use FinGather\Service\Provider\PortfolioProviderInterface;
use FinGather\Service\Provider\TickerDataProviderInterface;
use FinGather\Service\Provider\TickerProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteGet;
use MarekSkopal\Router\Attribute\RoutePost;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class AssetController
{
	public function __construct(
		private AssetProviderInterface $assetProvider,
		private AssetDataProviderInterface $assetDataProvider,
		private AssetWithPropertiesProvider $assetWithPropertiesProvider,
		private TickerProvider $tickerProvider,
		private TickerDataProviderInterface $tickerDataProvider,
		private PortfolioProviderInterface $portfolioProvider,
		private GroupProvider $groupProvider,
		private RequestService $requestService,
	) {
	}

	#[RouteGet(Routes::Assets->value)]
	public function actionGetAssets(ServerRequestInterface $request, int $portfolioId): ResponseInterface
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
		$assets = $this->assetProvider->getAssets($user, $portfolio);

		$assetDtos = [];

		foreach ($assets as $asset) {
			$lastTickerDataClose = $this->tickerDataProvider->getLastTickerDataClose($asset->ticker, $dateTime);
			if ($lastTickerDataClose === null) {
				continue;
			}

			$assetDtos[] = AssetDto::fromEntity($asset, $lastTickerDataClose);
		}

		return new JsonResponse($assetDtos);
	}

	#[RouteGet(Routes::AssetsWithProperties->value)]
	public function actionGetAssetsWithProperties(ServerRequestInterface $request, int $portfolioId): ResponseInterface
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

		/** @var array{orderBy?: string} $queryParams */
		$queryParams = $request->getQueryParams();
		$orderBy = ($queryParams['orderBy'] ?? null) !== null ?
			AssetOrderEnum::from($queryParams['orderBy']) :
			AssetOrderEnum::TickerName;

		return new JsonResponse($this->assetWithPropertiesProvider->getAssetsWithAssetData(
			$user,
			$portfolio,
			$dateTime,
			$orderBy,
		));
	}

	#[RouteGet(Routes::Asset->value)]
	public function actionGetAsset(ServerRequestInterface $request, int $assetId): ResponseInterface
	{
		if ($assetId < 1) {
			return new NotFoundResponse('Asset id is required.');
		}

		$user = $this->requestService->getUser($request);

		$asset = $this->assetProvider->getAsset(user: $user, assetId: $assetId);
		if ($asset === null) {
			return new NotFoundResponse('Asset with id "' . $assetId . '" was not found.');
		}

		$dateTime = new DateTimeImmutable();

		$assetData = $this->assetDataProvider->getAssetData($user, $asset->portfolio, $asset, $dateTime);
		if ($assetData === null) {
			$lastTickerDataClose = $this->tickerDataProvider->getLastTickerDataClose($asset->ticker, $dateTime);
			if ($lastTickerDataClose === null) {
				return new NotFoundResponse('Asset with id "' . $assetId . '" was not found.');
			}

			return new JsonResponse(AssetDto::fromEntity($asset, $lastTickerDataClose));
		}

		return new JsonResponse(AssetWithPropertiesDto::fromEntity($asset, $assetData, 0));
	}

	#[RoutePost(Routes::Assets->value)]
	public function actionCreateAsset(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		$assetCreateDto = $this->requestService->getRequestBodyDto($request, AssetCreateDto::class);

		$ticker = $this->tickerProvider->getTicker($assetCreateDto->tickerId);
		if ($ticker === null) {
			return new NotFoundResponse('Ticker with id "' . $assetCreateDto->tickerId . '" was not found.');
		}

		$dateTime = new DateTimeImmutable();
		$lastTickerDataClose = $this->tickerDataProvider->getLastTickerDataClose($ticker, $dateTime);
		if ($lastTickerDataClose === null) {
			return new NotFoundResponse('Ticker Data for ticker with id "' . $ticker->id . '" was not found.');
		}

		return new JsonResponse(AssetDto::fromEntity($this->assetProvider->createAsset(
			user: $user,
			portfolio: $portfolio,
			ticker: $ticker,
			othersGroup: $this->groupProvider->getOthersGroup($user, $portfolio),
		), $lastTickerDataClose));
	}
}
