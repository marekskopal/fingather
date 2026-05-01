<?php

declare(strict_types=1);

namespace FinGather\Controller\Admin;

use FinGather\Dto\ProxyAssetCreateDto;
use FinGather\Dto\ProxyAssetDto;
use FinGather\Response\ConflictResponse;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\ProxyAssetProviderInterface;
use FinGather\Service\Provider\TickerProviderInterface;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteDelete;
use MarekSkopal\Router\Attribute\RouteGet;
use MarekSkopal\Router\Attribute\RoutePost;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ProxyAssetController extends AdminController
{
	public function __construct(
		RequestService $requestService,
		private ProxyAssetProviderInterface $proxyAssetProvider,
		private TickerProviderInterface $tickerProvider,
	) {
		parent::__construct($requestService);
	}

	#[RouteGet(Routes::AdminProxyAssets->value)]
	public function actionGetProxyAssets(ServerRequestInterface $request): ResponseInterface
	{
		$proxyAssets = [];
		foreach ($this->proxyAssetProvider->getProxyAssets() as $proxyAsset) {
			$proxyAssets[] = ProxyAssetDto::fromEntity($proxyAsset);
		}

		usort($proxyAssets, fn(ProxyAssetDto $a, ProxyAssetDto $b): int => $a->tickerType->value <=> $b->tickerType->value);

		return new JsonResponse($proxyAssets);
	}

	#[RoutePost(Routes::AdminProxyAssets->value)]
	public function actionCreateProxyAsset(ServerRequestInterface $request): ResponseInterface
	{
		$proxyAssetCreateDto = $this->requestService->getRequestBodyDto($request, ProxyAssetCreateDto::class);

		$ticker = $this->tickerProvider->getTicker($proxyAssetCreateDto->tickerId);
		if ($ticker === null) {
			return new NotFoundResponse('Ticker with id "' . $proxyAssetCreateDto->tickerId . '" was not found.');
		}

		$existingProxyAsset = $this->proxyAssetProvider->getProxyAssetByTickerType($proxyAssetCreateDto->tickerType);
		if ($existingProxyAsset !== null) {
			return new ConflictResponse(
				'Proxy asset for ticker type "' . $proxyAssetCreateDto->tickerType->value . '" already exists.',
			);
		}

		$proxyAsset = $this->proxyAssetProvider->createProxyAsset($proxyAssetCreateDto->tickerType, $ticker);

		return new JsonResponse(ProxyAssetDto::fromEntity($proxyAsset));
	}

	#[RouteDelete(Routes::AdminProxyAsset->value)]
	public function actionDeleteProxyAsset(ServerRequestInterface $request, int $proxyAssetId): ResponseInterface
	{
		$proxyAsset = $this->proxyAssetProvider->getProxyAsset($proxyAssetId);
		if ($proxyAsset === null) {
			return new NotFoundResponse('Proxy asset with id "' . $proxyAssetId . '" was not found.');
		}

		$this->proxyAssetProvider->deleteProxyAsset($proxyAsset);

		return new OkResponse();
	}
}
