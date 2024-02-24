<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\ImportDataDto;
use FinGather\Dto\ImportPrepareDto;
use FinGather\Dto\ImportStartDto;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Service\Import\ImportService;
use FinGather\Service\Provider\BrokerProvider;
use FinGather\Service\Provider\ImportMappingProvider;
use FinGather\Service\Provider\ImportProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ImportController
{
	public function __construct(
		private readonly ImportService $importService,
		private readonly ImportProvider $importProvider,
		private readonly ImportMappingProvider $importMappingProvider,
		private readonly BrokerProvider $brokerProvider,
		private readonly RequestService $requestService,
	) {
	}

	public function actionImportPrepare(ServerRequestInterface $request): ResponseInterface
	{
		$importData = ImportDataDto::fromJson($request->getBody()->getContents());

		$broker = $this->brokerProvider->getBroker($this->requestService->getUser($request), $importData->brokerId);
		if ($broker === null) {
			return new NotFoundResponse('Broker was not found');
		}

		return new JsonResponse(ImportPrepareDto::fromImportPrepare($this->importService->prepareImport($broker, $importData->data)));
	}

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
