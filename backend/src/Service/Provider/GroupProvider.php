<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use ArrayIterator;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\AssetRepository;
use FinGather\Model\Repository\GroupRepository;
use Iterator;

final readonly class GroupProvider
{
	public function __construct(
		private GroupRepository $groupRepository,
		private AssetRepository $assetRepository,
		private GroupDataProvider $groupDataProvider,
	) {
	}

	/** @return Iterator<Group> */
	public function getGroups(User $user, Portfolio $portfolio): Iterator
	{
		return $this->groupRepository->findGroups($user->id, $portfolio->id);
	}

	public function getGroup(User $user, int $groupId): ?Group
	{
		return $this->groupRepository->findGroup($user->id, $groupId);
	}

	public function getOthersGroup(User $user, Portfolio $portfolio): Group
	{
		return $this->groupRepository->findOthersGroup($user->id, $portfolio->id);
	}

	/** @param list<int> $assetIds */
	public function createGroup(User $user, Portfolio $portfolio, string $name, string $color, array $assetIds): Group
	{
		$group = new Group(user: $user, portfolio: $portfolio, name: $name, color: $color, isOthers: false, assets: new ArrayIterator([]));
		$this->groupRepository->persist($group);

		foreach ($assetIds as $assetId) {
			$asset = $this->assetRepository->findAsset($assetId, $user->id);
			if ($asset === null) {
				continue;
			}

			$asset->group = $group;
			$this->assetRepository->persist($asset);
		}

		$this->groupDataProvider->deleteUserGroupData(user: $user, portfolio: $portfolio);

		return $group;
	}

	public function createOthersGroup(User $user, Portfolio $portfolio): Group
	{
		$group = new Group(
			user: $user,
			portfolio: $portfolio,
			name: Group::OthersName,
			color: Group::OthersColor,
			isOthers: true,
			assets: new ArrayIterator([]),
		);
		$this->groupRepository->persist($group);

		return $group;
	}

	/** @param list<int> $assetIds */
	public function updateGroup(Group $group, string $name, string $color, array $assetIds): Group
	{
		$group->name = $name;
		$group->color = $color;
		$this->groupRepository->persist($group);

		$user = $group->user;
		$portfolio = $group->portfolio;
		$othersGroup = $this->getOthersGroup($user, $portfolio);

		foreach ($group->assets as $asset) {
			$asset->group = $othersGroup;
			$this->assetRepository->persist($asset);
		}

		foreach ($assetIds as $assetId) {
			$asset = $this->assetRepository->findAsset($assetId, $user->id);
			if ($asset === null) {
				continue;
			}

			$asset->group = $group;
			$this->assetRepository->persist($asset);
		}

		$this->groupDataProvider->deleteUserGroupData(user: $user, portfolio: $portfolio);

		return $group;
	}

	public function deleteGroup(Group $group): void
	{
		$user = $group->user;
		$portfolio = $group->portfolio;
		$othersGroup = $this->getOthersGroup($user, $portfolio);

		foreach ($group->assets as $asset) {
			$asset->group = $othersGroup;
			$this->assetRepository->persist($asset);
		}

		$this->groupDataProvider->deleteUserGroupData(user: $user, portfolio: $portfolio);

		$this->groupRepository->delete($group);
	}
}
