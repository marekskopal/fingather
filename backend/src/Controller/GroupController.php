<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\GroupDto;
use FinGather\Model\Entity\Group;
use FinGather\Service\Provider\GroupProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GroupController
{
	public function __construct(private readonly GroupProvider $groupProvider, private readonly RequestService $requestService)
	{
	}

	public function actionGetGroups(ServerRequestInterface $request): ResponseInterface
	{
		$brokers = array_map(
			fn (Group $group): GroupDto => GroupDto::fromEntity($group),
			iterator_to_array($this->groupProvider->getGroups($this->requestService->getUser($request)))
		);

		return new JsonResponse($brokers);
	}
}
