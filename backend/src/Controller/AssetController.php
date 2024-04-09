<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\AssetDto;
use FinGather\Dto\AssetsWithPropertiesDto;
use FinGather\Dto\AssetWithPropertiesDto;
use FinGather\Dto\Enum\AssetOrderEnum;
use FinGather\Dto\TickerDto;
use FinGather\Response\NotFoundResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\AssetDataProvider;
use FinGather\Service\Provider\AssetProvider;
use FinGather\Service\Provider\MarketProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Provider\TickerDataProvider;
use FinGather\Service\Provider\TickerProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteGet;
use MarekSkopal\Router\Attribute\RoutePost;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Safe\DateTimeImmutable;

class AssetController
{
	public function __construct(
		private readonly AssetProvider $assetProvider,
		private readonly AssetDataProvider $assetDataProvider,
		private readonly TickerProvider $tickerProvider,
		private readonly TickerDataProvider $tickerDataProvider,
		private readonly PortfolioProvider $portfolioProvider,
		private readonly MarketProvider $marketProvider,
		private readonly RequestService $requestService,
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
			$lastTickerData = $this->tickerDataProvider->getLastTickerData($asset->getTicker(), $dateTime);
			if ($lastTickerData === null) {
				continue;
			}

			$assetDtos[] = AssetDto::fromEntity($asset, $lastTickerData->getClose());
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
		$assets = $this->assetProvider->getAssets(user: $user, portfolio: $portfolio);

		$openAssets = [];
		$closedAssets = [];
		$watchedAssets = [];

		foreach ($assets as $asset) {
			$assetData = $this->assetDataProvider->getAssetData($user, $portfolio, $asset, $dateTime);
			if ($assetData === null) {
				$lastTickerData = $this->tickerDataProvider->getLastTickerData($asset->getTicker(), $dateTime);
				assert($lastTickerData !== null);
				$watchedAssets[] = AssetDto::fromEntity($asset, $lastTickerData->getClose());

				continue;
			}

			$assetDto = AssetWithPropertiesDto::fromEntity($asset, $assetData);

			if ($assetData->isClosed()) {
				$closedAssets[] = $assetDto;
			} else {
				$openAssets[] = $assetDto;
			}
		}

		/** @var array{orderBy?: string} $queryParams */
		$queryParams = $request->getQueryParams();
		$orderBy = ($queryParams['orderBy'] ?? null) !== null ?
			AssetOrderEnum::from($queryParams['orderBy']) :
			AssetOrderEnum::TickerName;

		match ($orderBy) {
			AssetOrderEnum::TickerName => usort(
				$openAssets,
				fn (AssetWithPropertiesDto $a, AssetWithPropertiesDto $b) => $a->ticker->ticker <=> $b->ticker->ticker,
			),
			AssetOrderEnum::Price => usort(
				$openAssets,
				fn (AssetWithPropertiesDto $a, AssetWithPropertiesDto $b) => $b->price <=> $a->price,
			),
			AssetOrderEnum::Units => usort(
				$openAssets,
				fn (AssetWithPropertiesDto $a, AssetWithPropertiesDto $b) => $b->units <=> $a->units,
			),
			AssetOrderEnum::Value => usort(
				$openAssets,
				fn (AssetWithPropertiesDto $a, AssetWithPropertiesDto $b) => $b->value <=> $a->value,
			),
			AssetOrderEnum::Gain => usort(
				$openAssets,
				fn (AssetWithPropertiesDto $a, AssetWithPropertiesDto $b) => $b->gainDefaultCurrency <=> $a->gainDefaultCurrency,
			),
			AssetOrderEnum::DividendGain => usort(
				$openAssets,
				fn (AssetWithPropertiesDto $a, AssetWithPropertiesDto $b) => $b->dividendGainDefaultCurrency <=> $a->dividendGainDefaultCurrency,
			),
			AssetOrderEnum::FxImpact => usort(
				$openAssets,
				fn (AssetWithPropertiesDto $a, AssetWithPropertiesDto $b) => $b->fxImpact <=> $a->fxImpact,
			),
			AssetOrderEnum::Return => usort(
				$openAssets,
				fn (AssetWithPropertiesDto $a, AssetWithPropertiesDto $b) => $b->return <=> $a->return,
			),
		};

		return new JsonResponse(new AssetsWithPropertiesDto(
			openAssets: $openAssets,
			closedAssets: $closedAssets,
			watchedAssets: $watchedAssets,
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

		$assetData = $this->assetDataProvider->getAssetData($user, $asset->getPortfolio(), $asset, $dateTime);
		if ($assetData === null) {
			$lastTickerData = $this->tickerDataProvider->getLastTickerData($asset->getTicker(), $dateTime);
			if ($lastTickerData === null) {
				return new NotFoundResponse('Asset with id "' . $assetId . '" was not found.');
			}

			return new JsonResponse(AssetDto::fromEntity($asset, $lastTickerData->getClose()));
		}

		return new JsonResponse(AssetWithPropertiesDto::fromEntity($asset, $assetData));
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
		), $lastTickerData->getClose()));
	}
}
