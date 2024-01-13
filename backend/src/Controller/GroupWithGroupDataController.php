<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Service\Provider\GroupWithGroupDataProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Safe\DateTimeImmutable;

class GroupWithGroupDataController
{
	public function __construct(
		private readonly GroupWithGroupDataProvider $groupWithGroupDataProvider,
		private readonly RequestService $requestService,
	)
	{
	}

	public function actionGetGroupsWithGroupData(ServerRequestInterface $request): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		$dateTime = new DateTimeImmutable();

		return new JsonResponse($this->groupWithGroupDataProvider->getGroupsWithGroupData($user, $dateTime));
	}
}
