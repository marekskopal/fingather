<?php

declare(strict_types=1);

namespace FinGather\Controller;

use Decimal\Decimal;
use FinGather\Dto\AssetDto;
use FinGather\Dto\AssetsWithPropertiesDto;
use FinGather\Dto\AssetWithPropertiesDto;
use FinGather\Dto\TickerDto;
use FinGather\Response\NotFoundResponse;
use FinGather\Service\Provider\AssetProvider;
use FinGather\Service\Provider\MarketProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Provider\TickerDataProvider;
use FinGather\Service\Provider\TickerProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Safe\DateTimeImmutable;

class AssetController
{
	public function __construct(
		private readonly AssetProvider $assetProvider,
		private readonly TickerProvider $tickerProvider,
		private readonly TickerDataProvider $tickerDataProvider,
		private readonly PortfolioProvider $portfolioProvider,
		private readonly MarketProvider $marketProvider,
		private readonly RequestService $requestService,
	) {
	}

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
			$lastTickerData = $this->tickerDataProvider->getLastTickerData($asset->getTicker(), $dateTime);
			if ($lastTickerData === null) {
				continue;
			}

			$assetDtos[] = AssetDto::fromEntity($asset, new Decimal($lastTickerData->getClose()));
		}

		return new JsonResponse($assetDtos);
	}

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
		$assets = $this->assetProvider->getAssets(user: $user, portfolio: $portfolio, dateTime: $dateTime);

		$openAssets = [];
		$closedAssets = [];
		$watchedAssets = [];

		foreach ($assets as $asset) {
			$assetProperties = $this->assetProvider->getAssetProperties($user, $portfolio, $asset, $dateTime);
			if ($assetProperties === null) {
				$lastTickerData = $this->tickerDataProvider->getLastTickerData($asset->getTicker(), $dateTime);
				assert($lastTickerData !== null);
				$watchedAssets[] = AssetDto::fromEntity($asset, new Decimal($lastTickerData->getClose()));

				continue;
			}

			$assetDto = AssetWithPropertiesDto::fromEntity($asset, $assetProperties);

			if ($assetProperties->isClosed()) {
				$closedAssets[] = $assetDto;
			} else {
				$openAssets[] = $assetDto;
			}
		}

		return new JsonResponse(new AssetsWithPropertiesDto(
			openAssets: $openAssets,
			closedAssets: $closedAssets,
			watchedAssets: $watchedAssets,
		));
	}

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

		$assetProperties = $this->assetProvider->getAssetProperties($user, $asset->getPortfolio(), $asset, $dateTime);
		if ($assetProperties === null) {
			$lastTickerData = $this->tickerDataProvider->getLastTickerData($asset->getTicker(), $dateTime);
			if ($lastTickerData === null) {
				return new NotFoundResponse('Asset with id "' . $assetId . '" was not found.');
			}

			return new JsonResponse(AssetDto::fromEntity($asset, new Decimal($lastTickerData->getClose())));
		}

		return new JsonResponse(AssetWithPropertiesDto::fromEntity($asset, $assetProperties));
	}

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

		$tickerDto = TickerDto::fromJson($request->getBody()->getContents());

		$market = $this->marketProvider->getMarketByMic($tickerDto->market->mic);
		if ($market === null) {
			return new NotFoundResponse('Market with MIC "' . $tickerDto->market->mic . '" was not found.');
		}

		$ticker = $this->tickerProvider->getTickerByTicker($tickerDto->ticker, $market);
		if ($ticker === null) {
			return new NotFoundResponse('Ticker "' . $tickerDto->ticker . '" was not found.');
		}

		$dateTime = new DateTimeImmutable();
		$lastTickerData = $this->tickerDataProvider->getLastTickerData($ticker, $dateTime);
		if ($lastTickerData === null) {
			return new NotFoundResponse('Ticker Data for ticker "' . $tickerDto->ticker . '" was not found.');
		}

		return new JsonResponse(AssetDto::fromEntity($this->assetProvider->createAsset(
			user: $user,
			portfolio: $portfolio,
			ticker: $ticker,
		), new Decimal($lastTickerData->getClose())));
	}
}
