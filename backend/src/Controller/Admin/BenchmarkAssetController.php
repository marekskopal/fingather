<?php

declare(strict_types=1);

namespace FinGather\Controller\Admin;

use FinGather\Dto\BenchmarkAssetCreateDto;
use FinGather\Dto\BenchmarkAssetDto;
use FinGather\Response\ConflictResponse;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\BenchmarkAssetProvider;
use FinGather\Service\Provider\TickerProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteDelete;
use MarekSkopal\Router\Attribute\RouteGet;
use MarekSkopal\Router\Attribute\RoutePost;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class BenchmarkAssetController extends AdminController
{
	public function __construct(
		RequestService $requestService,
		private readonly BenchmarkAssetProvider $benchmarkAssetProvider,
		private readonly TickerProvider $tickerProvider,
	) {
		parent::__construct($requestService);
	}

	#[RouteGet(Routes::AdminBenchmarkAssets->value)]
	public function actionGetBenchmarkAssets(ServerRequestInterface $request): ResponseInterface
	{
		$this->checkAdminRole($request);

		$benchmarkAssets = [];
		foreach ($this->benchmarkAssetProvider->getBenchmarkAssets() as $benchmarkAsset) {
			$benchmarkAssets[] = BenchmarkAssetDto::fromEntity($benchmarkAsset);
		}

		usort($benchmarkAssets, fn(BenchmarkAssetDto $a, BenchmarkAssetDto $b): int => $a->ticker->name <=> $b->ticker->name);

		return new JsonResponse($benchmarkAssets);
	}

	#[RoutePost(Routes::AdminBenchmarkAssets->value)]
	public function actionCreateBenchmarkAsset(ServerRequestInterface $request): ResponseInterface
	{
		$this->checkAdminRole($request);

		$benchmarkAssetCreateDto = $this->requestService->getRequestBodyDto($request, BenchmarkAssetCreateDto::class);

		$ticker = $this->tickerProvider->getTicker($benchmarkAssetCreateDto->tickerId);
		if ($ticker === null) {
			return new NotFoundResponse('Ticker with id "' . $benchmarkAssetCreateDto->tickerId . '" was not found.');
		}

		$existingBenchmarkAsset = $this->benchmarkAssetProvider->getBenchmarkAssetByTickerId($benchmarkAssetCreateDto->tickerId);
		if ($existingBenchmarkAsset !== null) {
			return new ConflictResponse('Benchmark asset with ticker "' . $ticker->ticker . '" already exists.');
		}

		$benchmarkAsset = $this->benchmarkAssetProvider->createBenchmarkAsset($ticker);

		return new JsonResponse(BenchmarkAssetDto::fromEntity($benchmarkAsset));
	}

	#[RouteDelete(Routes::AdminBenchmarkAsset->value)]
	public function actionDeleteBenchmarkAsset(ServerRequestInterface $request, int $benchmarkAssetId): ResponseInterface
	{
		$this->checkAdminRole($request);

		$benchmarkAsset = $this->benchmarkAssetProvider->getBenchmarkAsset($benchmarkAssetId);
		if ($benchmarkAsset === null) {
			return new NotFoundResponse('Benchmark asset with id "' . $benchmarkAssetId . '" was not found.');
		}

		$this->benchmarkAssetProvider->deleteBenchmarkAsset($benchmarkAsset);

		return new OkResponse();
	}
}
