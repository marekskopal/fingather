<?php

declare(strict_types=1);

namespace FinGather\Controller;

use FinGather\Dto\GroupDto;
use FinGather\Model\Entity\Group;
use FinGather\Response\NotFoundResponse;
use FinGather\Response\OkResponse;
use FinGather\Route\Routes;
use FinGather\Service\Provider\GroupProvider;
use FinGather\Service\Provider\PortfolioProvider;
use FinGather\Service\Request\RequestService;
use Laminas\Diactoros\Response\JsonResponse;
use MarekSkopal\Router\Attribute\RouteDelete;
use MarekSkopal\Router\Attribute\RouteGet;
use MarekSkopal\Router\Attribute\RoutePost;
use MarekSkopal\Router\Attribute\RoutePut;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class GroupController
{
	public function __construct(
		private readonly GroupProvider $groupProvider,
		private readonly PortfolioProvider $portfolioProvider,
		private readonly RequestService $requestService,
	) {
	}

	#[RouteGet(Routes::Groups->value)]
	public function actionGetGroups(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		$groups = array_map(
			fn (Group $group): GroupDto => GroupDto::fromEntity($group),
			$this->groupProvider->getGroups($user, $portfolio),
		);

		return new JsonResponse($groups);
	}

	#[RouteGet(Routes::Group->value)]
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

	#[RouteGet(Routes::GroupOthers->value)]
	public function actionGetOthersGroup(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		$othersGroup = $this->groupProvider->getOthersGroup($user, $portfolio);

		return new JsonResponse(GroupDto::fromEntity($othersGroup));
	}

	#[RoutePost(Routes::Groups->value)]
	public function actionPostGroup(ServerRequestInterface $request, int $portfolioId): ResponseInterface
	{
		$user = $this->requestService->getUser($request);

		if ($portfolioId < 1) {
			return new NotFoundResponse('Portfolio id is required.');
		}

		$portfolio = $this->portfolioProvider->getPortfolio($user, $portfolioId);
		if ($portfolio === null) {
			return new NotFoundResponse('Portfolio with id "' . $portfolioId . '" was not found.');
		}

		/** @var array{name: string, color: string, assetIds: list<int>} $requestBody */
		$requestBody = json_decode($request->getBody()->getContents(), associative: true);

		return new JsonResponse(GroupDto::fromEntity($this->groupProvider->createGroup(
			user: $user,
			portfolio: $portfolio,
			name: $requestBody['name'],
			color: $requestBody['color'],
			assetIds: $requestBody['assetIds'],
		)));
	}

	#[RoutePut(Routes::Group->value)]
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

		/** @var array{name: string, color: string, assetIds: list<int>} $requestBody */
		$requestBody = json_decode($request->getBody()->getContents(), associative: true);

		return new JsonResponse(GroupDto::fromEntity($this->groupProvider->updateGroup(
			group: $group,
			name: $requestBody['name'],
			color: $requestBody['color'],
			assetIds: $requestBody['assetIds'],
		)));
	}

	#[RouteDelete(Routes::Group->value)]
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
