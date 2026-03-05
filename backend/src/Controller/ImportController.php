<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\ImportMappingResponseDto;
use FinGather\Dto\ImportMappingUpdateDto;
use FinGather\Dto\ImportPrepareDataDto;
use FinGather\Dto\ImportPrepareDto;
use FinGather\Dto\ImportStartDto;
use FinGather\Model\Entity\ImportMapping;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Route\Routes;
use FinGather\Service\Import\ImportPrepareService;
use FinGather\Service\Import\ImportService;
use FinGather\Service\Provider\ImportFileProvider;
use FinGather\Service\Provider\ImportMappingProvider;
use FinGather\Service\Provider\ImportProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Provider\TickerProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteDelete;
use MarekSkopal\Router\Attribute\RouteGet;
use MarekSkopal\Router\Attribute\RoutePost;
use MarekSkopal\Router\Attribute\RoutePut;
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
		private TickerProvider $tickerProvider,
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

	#[RouteGet(Routes::ImportMappings->value)]
	public function actionGetImportMappings(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		$importMappings = array_map(
			fn (ImportMapping $importMapping): ImportMappingResponseDto => ImportMappingResponseDto::fromEntity($importMapping),
			iterator_to_array($this->importMappingProvider->getPortfolioImportMappings($user, $portfolio), false),
		);

		return new JsonResponse($importMappings);
	}

	#[RouteGet(Routes::ImportMapping->value)]
	public function actionGetImportMapping(ServerRequestInterface $request, int $importMappingId): ResponseInterface
	{
		if ($importMappingId < 1) {
			return new NotFoundResponse('Import mapping id is required.');
		}

		$importMapping = $this->importMappingProvider->getImportMapping(
			user: $this->requestService->getUser($request),
			importMappingId: $importMappingId,
		);
		if ($importMapping === null) {
			return new NotFoundResponse('Import mapping with id "' . $importMappingId . '" was not found.');
		}

		return new JsonResponse(ImportMappingResponseDto::fromEntity($importMapping));
	}

	#[RoutePut(Routes::ImportMapping->value)]
	public function actionPutImportMapping(ServerRequestInterface $request, int $importMappingId): ResponseInterface
	{
		if ($importMappingId < 1) {
			return new NotFoundResponse('Import mapping id is required.');
		}

		$importMapping = $this->importMappingProvider->getImportMapping(
			user: $this->requestService->getUser($request),
			importMappingId: $importMappingId,
		);
		if ($importMapping === null) {
			return new NotFoundResponse('Import mapping with id "' . $importMappingId . '" was not found.');
		}

		$updateDto = $this->requestService->getRequestBodyDto($request, ImportMappingUpdateDto::class);

		$ticker = $this->tickerProvider->getTicker($updateDto->tickerId);
		if ($ticker === null) {
			return new NotFoundResponse('Ticker with id "' . $updateDto->tickerId . '" was not found.');
		}

		return new JsonResponse(ImportMappingResponseDto::fromEntity(
			$this->importMappingProvider->updateImportMapping($importMapping, $ticker),
		));
	}

	#[RouteDelete(Routes::ImportMapping->value)]
	public function actionDeleteImportMapping(ServerRequestInterface $request, int $importMappingId): ResponseInterface
	{
		if ($importMappingId < 1) {
			return new NotFoundResponse('Import mapping id is required.');
		}

		$importMapping = $this->importMappingProvider->getImportMapping(
			user: $this->requestService->getUser($request),
			importMappingId: $importMappingId,
		);
		if ($importMapping === null) {
			return new NotFoundResponse('Import mapping with id "' . $importMappingId . '" was not found.');
		}

		$this->importMappingProvider->deleteImportMapping($importMapping);

		return new OkResponse();
	}
}
