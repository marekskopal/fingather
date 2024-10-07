<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\AssetRepository;
use FinGather\Model\Repository\GroupRepository;

class GroupProvider
{
	public function __construct(
		private readonly GroupRepository $groupRepository,
		private readonly AssetRepository $assetRepository,
		private readonly GroupDataProvider $groupDataProvider,
	) {
	}

	/** @return list<Group> */
	public function getGroups(User $user, Portfolio $portfolio): array
	{
		return $this->groupRepository->findGroups($user->getId(), $portfolio->getId());
	}

	public function getGroup(User $user, int $groupId): ?Group
	{
		return $this->groupRepository->findGroup($user->getId(), $groupId);
	}

	public function getOthersGroup(User $user, Portfolio $portfolio): Group
	{
		return $this->groupRepository->findOthersGroup($user->getId(), $portfolio->getId());
	}

	/** @param list<int> $assetIds */
	public function createGroup(User $user, Portfolio $portfolio, string $name, string $color, array $assetIds): Group
	{
		$group = new Group(user: $user, portfolio: $portfolio, name: $name, color: $color, isOthers: false, assets: []);
		$this->groupRepository->persist($group);

		foreach ($assetIds as $assetId) {
			$asset = $this->assetRepository->findAsset($assetId, $user->getId());
			if ($asset === null) {
				continue;
			}

			$asset->setGroup($group);
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
			assets: [],
		);
		$this->groupRepository->persist($group);

		return $group;
	}

	/** @param list<int> $assetIds */
	public function updateGroup(Group $group, string $name, string $color, array $assetIds): Group
	{
		$group->setName($name);
		$group->setColor($color);
		$this->groupRepository->persist($group);

		$user = $group->getUser();
		$portfolio = $group->getPortfolio();
		$othersGroup = $this->getOthersGroup($user, $portfolio);

		foreach ($group->getAssets() as $asset) {
			$asset->setGroup($othersGroup);
			$this->assetRepository->persist($asset);
		}

		foreach ($assetIds as $assetId) {
			$asset = $this->assetRepository->findAsset($assetId, $user->getId());
			if ($asset === null) {
				continue;
			}

			$asset->setGroup($group);
			$this->assetRepository->persist($asset);
		}

		$this->groupDataProvider->deleteUserGroupData(user: $user, portfolio: $portfolio);

		return $group;
	}

	public function deleteGroup(Group $group): void
	{
		$user = $group->getUser();
		$portfolio = $group->getPortfolio();
		$othersGroup = $this->getOthersGroup($user, $portfolio);

		foreach ($group->getAssets() as $asset) {
			$asset->setGroup($othersGroup);
			$this->assetRepository->persist($asset);
		}

		$this->groupDataProvider->deleteUserGroupData(user: $user, portfolio: $portfolio);

		$this->groupRepository->delete($group);
	}
}
