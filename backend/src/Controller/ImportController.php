<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\ImportPrepareDataDto;
use FinGather\Dto\ImportPrepareDto;
use FinGather\Dto\ImportStartDto;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Route\Routes;
use FinGather\Service\Import\ImportPrepareService;
use FinGather\Service\Import\ImportService;
use FinGather\Service\Provider\ImportFileProvider;
use FinGather\Service\Provider\ImportMappingProvider;
use FinGather\Service\Provider\ImportProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteDelete;
use MarekSkopal\Router\Attribute\RoutePost;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ImportController
{
	public function __construct(
		private ImportService $importService,
		private ImportPrepareService $importPrepareService,
		private ImportProvider $importProvider,
		private ImportFileProvider $importFileProvider,
		private ImportMappingProvider $importMappingProvider,
		private PortfolioProvider $portfolioProvider,
		private RequestService $requestService,
	) {
	}

	#[RoutePost(Routes::ImportPrepare->value)]
	public function actionImportPrepare(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		$importData = $this->requestService->getRequestBodyDto($request, ImportPrepareDataDto::class);

		$user = $this->requestService->getUser($request);

		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		try {
			$importPrepare = $this->importPrepareService->prepareImport($user, $portfolio, $importData);
		} catch (\RuntimeException $e) {
			return new NotFoundResponse('Imported file is not supported.');
		}

		return new JsonResponse(ImportPrepareDto::fromImportPrepare($importPrepare));
	}

	#[RoutePost(Routes::ImportStart->value)]
	public function actionImportStart(ServerRequestInterface $request): ResponseInterface
	{
		$importStart = $this->requestService->getRequestBodyDto($request, ImportStartDto::class);

		$user = $this->requestService->getUser($request);

		$import = $this->importProvider->getImportByUuid($user, $importStart->uuid);
		if ($import === null) {
			return new NotFoundResponse('Import was not found');
		}

		$this->importMappingProvider->createImportMappingFromImportStart($user, $importStart);

		$this->importService->importDataFiles($import);

		return new OkResponse();
	}

	#[RouteDelete(Routes::ImportImportFile->value)]
	public function actionDeleteImportFile(ServerRequestInterface $request, int $importFileId): ResponseInterface
	{
		if ($importFileId < 1) {
			return new NotFoundResponse('Import file id is required.');
		}

		$importFile = $this->importFileProvider->getImportFile(
			importFileId: $importFileId,
			user: $this->requestService->getUser($request),
		);
		if ($importFile === null) {
			return new NotFoundResponse('Import file with id "' . $importFileId . '" was not found.');
		}

		$this->importFileProvider->deleteImportFile($importFile);

		return new OkResponse();
	}
}
