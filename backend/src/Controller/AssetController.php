<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\AssetDto;
use FinGather\Service\Provider\AssetProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Safe\DateTime;

class AssetController
{
	public function __construct(private readonly AssetProvider $assetProvider, private readonly RequestService $requestService)
	{
	}

	public function actionGetAssets(ServerRequestInterface $request): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		$dateTime = new DateTime();
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
}
