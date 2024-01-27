<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\GroupDto;
use FinGather\Model\Entity\Group;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Service\Provider\GroupProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function Safe\json_decode;

class GroupController
{
	public function __construct(private readonly GroupProvider $groupProvider, private readonly RequestService $requestService)
	{
	}

	public function actionGetGroups(ServerRequestInterface $request): ResponseInterface
	{
		$brokers = array_map(
			fn (Group $group): GroupDto => GroupDto::fromEntity($group),
			iterator_to_array($this->groupProvider->getGroups($this->requestService->getUser($request))),
		);

		return new JsonResponse($brokers);
	}

	public function actionGetGroup(ServerRequestInterface $request, int $groupId): ResponseInterface
	{
		if ($groupId < 1) {
			return new NotFoundResponse('Group id is required.');
		}

		$group = $this->groupProvider->getGroup(
			user: $this->requestService->getUser($request),
			groupId: $groupId,
		);
		if ($group === null) {
			return new NotFoundResponse('Group with id "' . $groupId . '" was not found.');
		}

		return new JsonResponse(GroupDto::fromEntity($group));
	}

	public function actionGetOthersGroup(ServerRequestInterface $request): ResponseInterface
	{
		$othersGroup = $this->groupProvider->getOthersGroup($this->requestService->getUser($request));

		return new JsonResponse(GroupDto::fromEntity($othersGroup));
	}

	public function actionPostGroup(ServerRequestInterface $request): ResponseInterface
	{
		/** @var array{name: string, assetIds: list<int>} $requestBody */
		$requestBody = json_decode($request->getBody()->getContents(), assoc: true);

		return new JsonResponse(GroupDto::fromEntity($this->groupProvider->createGroup(
			user: $this->requestService->getUser($request),
			name: $requestBody['name'],
			assetIds: $requestBody['assetIds'],
		)));
	}

	public function actionPutGroup(ServerRequestInterface $request, int $groupId): ResponseInterface
	{
		if ($groupId < 1) {
			return new NotFoundResponse('Group id is required.');
		}

		$group = $this->groupProvider->getGroup(
			user: $this->requestService->getUser($request),
			groupId: $groupId,
		);
		if ($group === null) {
			return new NotFoundResponse('Group with id "' . $groupId . '" was not found.');
		}

		/** @var array{name: string, assetIds: list<int>} $requestBody */
		$requestBody = json_decode($request->getBody()->getContents(), assoc: true);

		return new JsonResponse(GroupDto::fromEntity($this->groupProvider->updateGroup(
			group: $group,
			name: $requestBody['name'],
			assetIds: $requestBody['assetIds'],
		)));
	}

	public function actionDeleteGroup(ServerRequestInterface $request, int $groupId): ResponseInterface
	{
		if ($groupId < 1) {
			return new NotFoundResponse('Group id is required.');
		}

		$group = $this->groupProvider->getGroup(
			user: $this->requestService->getUser($request),
			groupId: $groupId,
		);
		if ($group === null) {
			return new NotFoundResponse('Group with id "' . $groupId . '" was not found.');
		}

		$this->groupProvider->deleteGroup($group);

		return new OkResponse();
	}
}
