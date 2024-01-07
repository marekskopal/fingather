<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\ImportDataDto;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Service\Import\ImportService;
use FinGather\Service\Provider\BrokerProvider;
use FinGather\Service\Request\RequestService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ImportDataController
{
	public function __construct(
		private readonly ImportService $importService,
		private readonly BrokerProvider $brokerProvider,
		private readonly RequestService $requestService,
	) {
	}

	public function actionImportData(ServerRequestInterface $request): ResponseInterface
	{
		$importData = ImportDataDto::fromJson($request->getBody()->getContents());

		$broker = $this->brokerProvider->getBroker($this->requestService->getUser($request), $importData->brokerId);
		if ($broker === null) {
			return new NotFoundResponse('Broker was not found');
		}

		$this->importService->importCsv($broker, $importData->data);

		return new OkResponse();
	}
}
