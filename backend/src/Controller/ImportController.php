<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\ImportDataDto;
use FinGather\Dto\ImportPrepareDto;
use FinGather\Dto\ImportStartDto;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Route\Routes;
use FinGather\Service\Import\ImportService;
use FinGather\Service\Provider\ImportMappingProvider;
use FinGather\Service\Provider\ImportProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RoutePost;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ImportController
{
	public function __construct(
		private readonly ImportService $importService,
		private readonly ImportProvider $importProvider,
		private readonly ImportMappingProvider $importMappingProvider,
		private readonly PortfolioProvider $portfolioProvider,
		private readonly RequestService $requestService,
	) {
	}

	#[RoutePost(Routes::ImportPrepare->value)]
	public function actionImportPrepare(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		$importData = ImportDataDto::fromJson($request->getBody()->getContents());

		$user = $this->requestService->getUser($request);

		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		return new JsonResponse(ImportPrepareDto::fromImportPrepare(
			$this->importService->prepareImport($user, $portfolio, $importData),
		));
	}

	#[RoutePost(Routes::ImportStart->value)]
	public function actionImportStart(ServerRequestInterface $request): ResponseInterface
	{
		$importStart = ImportStartDto::fromJson($request->getBody()->getContents());

		$user = $this->requestService->getUser($request);

		$import = $this->importProvider->getImport($user, $importStart->importId);
		if ($import === null) {
			return new NotFoundResponse('Import was not found');
		}

		$this->importMappingProvider->createImportMappingFromImportStart($user, $importStart);

		$this->importService->importCsv($import);

		return new OkResponse();
	}
}
