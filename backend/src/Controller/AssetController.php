<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\BrokerDto;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Broker;
use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Service\Provider\AssetProvider;
use FinGather\Service\Provider\BrokerProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Safe\DateTime;
use function Safe\json_decode;

class AssetController
{
	public function __construct(private readonly AssetProvider $assetProvider, private readonly RequestService $requestService)
	{
	}

	public function actionGetAssets(ServerRequestInterface $request): ResponseInterface
	{
		/*$brokers = array_map(
			fn (Asset $broker): AssetD => BrokerDto::fromEntity($broker),
			iterator_to_array($this->assetProvider->getAssets($this->requestService->getUser($request), new DateTime()))
		);*/

		return new JsonResponse($brokers);
	}
}
