<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\AssetDto;
use FinGather\Response\NotFoundResponse;
use FinGather\Service\Provider\AssetProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Safe\DateTimeImmutable;

class AssetController
{
	public function __construct(private readonly AssetProvider $assetProvider, private readonly RequestService $requestService)
	{
	}

	public function actionGetAssets(ServerRequestInterface $request): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		$dateTime = new DateTimeImmutable();
		$assets = $this->assetProvider->getAssets($this->requestService->getUser($request), $dateTime);

		$assetDtos = [];

		foreach ($assets as $asset) {
			$assetProperties = $this->assetProvider->getAssetProperties($user, $asset, $dateTime);
			if ($assetProperties === null) {
				continue;
			}

			$assetDtos[] = AssetDto::fromEntity($asset, $assetProperties);
		}

		return new JsonResponse($assetDtos);
	}

	/** @param array{assetId: string} $args */
	public function actionGetAsset(ServerRequestInterface $request, array $args): ResponseInterface
	{
		$assetId = (int) $args['assetId'];
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
			return new NotFoundResponse('Asset with id "' . $assetId . '" was not found.');
		}

		return new JsonResponse(AssetDto::fromEntity($asset, $assetProperties));
	}
}
