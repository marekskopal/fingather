<?php

declare(strict_types=1);

namespace FinGather\Controller;

use Decimal\Decimal;
use FinGather\Dto\AssetDto;
use FinGather\Dto\AssetWithPropertiesDto;
use FinGather\Response\NotFoundResponse;
use FinGather\Service\Provider\AssetProvider;
use FinGather\Service\Provider\TickerDataProvider;
use FinGather\Service\Provider\TickerProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Safe\DateTimeImmutable;
use function Safe\json_decode;

class AssetController
{
	public function __construct(
		private readonly AssetProvider $assetProvider,
		private readonly TickerProvider $tickerProvider,
		private readonly TickerDataProvider $tickerDataProvider,
		private readonly RequestService $requestService,
	) {
	}

	public function actionGetAssets(ServerRequestInterface $request): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		$dateTime = new DateTimeImmutable();
		$assets = $this->assetProvider->getAssets($user);

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

	public function actionGetAssetsOpened(ServerRequestInterface $request): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		$dateTime = new DateTimeImmutable();
		$assets = $this->assetProvider->getOpenAssets($user, $dateTime);

		$assetDtos = [];

		foreach ($assets as $asset) {
			$assetProperties = $this->assetProvider->getAssetProperties($user, $asset, $dateTime);
			if ($assetProperties === null) {
				continue;
			}

			$assetDtos[] = AssetWithPropertiesDto::fromEntity($asset, $assetProperties);
		}

		return new JsonResponse($assetDtos);
	}

	public function actionGetAssetsClosed(ServerRequestInterface $request): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		$dateTime = new DateTimeImmutable();
		$assets = $this->assetProvider->getClosedAssets($user, $dateTime);

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

	public function actionGetAssetsWatched(ServerRequestInterface $request): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		$dateTime = new DateTimeImmutable();
		$assets = $this->assetProvider->getWatchedAssets($user);

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

		$assetProperties = $this->assetProvider->getAssetProperties($user, $asset, $dateTime);
		if ($assetProperties === null) {
			$lastTickerData = $this->tickerDataProvider->getLastTickerData($asset->getTicker(), $dateTime);
			if ($lastTickerData === null) {
				return new NotFoundResponse('Asset with id "' . $assetId . '" was not found.');
			}

			return new JsonResponse(AssetDto::fromEntity($asset, new Decimal($lastTickerData->getClose())));
		}

		return new JsonResponse(AssetWithPropertiesDto::fromEntity($asset, $assetProperties));
	}

	public function actionCreateAsset(ServerRequestInterface $request): ResponseInterface
	{
		/** @var array{ticker: string} $requestBody */
		$requestBody = json_decode($request->getBody()->getContents(), assoc: true);

		$ticker = $this->tickerProvider->getOrCreateTicker($requestBody['ticker']);
		if ($ticker === null) {
			return new NotFoundResponse('Ticker "' . $requestBody['ticker'] . '" was not found.');
		}

		$dateTime = new DateTimeImmutable();
		$lastTickerData = $this->tickerDataProvider->getLastTickerData($ticker, $dateTime);
		if ($lastTickerData === null) {
			return new NotFoundResponse('Ticker Data for ticker "' . $requestBody['ticker'] . '" was not found.');
		}

		return new JsonResponse(AssetDto::fromEntity($this->assetProvider->createAsset(
			user: $this->requestService->getUser($request),
			ticker: $ticker,
		), new Decimal($lastTickerData->getClose())));
	}
}
