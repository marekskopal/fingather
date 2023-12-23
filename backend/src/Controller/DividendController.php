<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\DividendDto;
use FinGather\Model\Entity\Dividend;
use FinGather\Service\Provider\AssetProvider;
use FinGather\Service\Provider\DividendProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DividendController
{
	public function __construct(
		private readonly DividendProvider $dividendProvider,
		private readonly AssetProvider $assetProvider,
		private readonly RequestService $requestService
	) {
	}

	public function actionGetDividends(ServerRequestInterface $request): ResponseInterface
	{
		$queryParams = $request->getQueryParams();

		$user = $this->requestService->getUser($request);

		$assetId = $queryParams['assetId'] ?? null ? (int) $queryParams['assetId'] : null;
		$asset = $assetId !== null ?
			$this->assetProvider->getAsset($user, $assetId) :
			null;

		$dividends = $asset !== null ?
			$this->dividendProvider->getAssetDividends($user, $asset) :
			$this->dividendProvider->getDividends($user);

		$dividendDtos = array_map(
			fn (Dividend $dividend): DividendDto => DividendDto::fromEntity($dividend),
			$dividends
		);

		return new JsonResponse($dividendDtos);
	}
}
