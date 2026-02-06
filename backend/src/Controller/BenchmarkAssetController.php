<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\BenchmarkAssetDto;
use FinGather\Route\Routes;
use FinGather\Service\Provider\BenchmarkAssetProvider;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteGet;
use Psr\Http\Message\ResponseInterface;

final class BenchmarkAssetController
{
	public function __construct(private readonly BenchmarkAssetProvider $benchmarkAssetProvider)
	{
	}

	#[RouteGet(Routes::BenchmarkAssets->value)]
	public function actionGetBenchmarkAssets(): ResponseInterface
	{
		$benchmarkAssets = [];
		foreach ($this->benchmarkAssetProvider->getBenchmarkAssets() as $benchmarkAsset) {
			$benchmarkAssets[] = BenchmarkAssetDto::fromEntity($benchmarkAsset);
		}

		usort($benchmarkAssets, fn(BenchmarkAssetDto $a, BenchmarkAssetDto $b): int => $a->ticker->name <=> $b->ticker->name);

		return new JsonResponse($benchmarkAssets);
	}
}
