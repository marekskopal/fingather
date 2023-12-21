<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\AssetRepository;
use FinGather\Model\Repository\GroupRepository;

class GroupProvider
{
	public function __construct(private readonly GroupRepository $groupRepository, private readonly AssetRepository $assetRepository)
	{
	}

	/** @return iterable<Group> */
	public function getGroups(User $user): iterable
	{
		return $this->groupRepository->findGroups($user->getId());
	}

	public function getGroup(User $user, int $groupId): ?Group
	{
		return $this->groupRepository->findGroup($user->getId(), $groupId);
	}

	public function getOthersGroup(User $user): Group
	{
		return $this->groupRepository->findOthersGroup($user->getId());
	}

	/** @param list<int> $assetIds */
	public function createGroup(User $user, string $name, array $assetIds): Group
	{
		$group = new Group(user: $user, name: $name, isOthers: false, assets: []);
		$this->groupRepository->persist($group);

		foreach ($assetIds as $assetId) {
			$asset = $this->assetRepository->findAsset($assetId, $user->getId());
			if ($asset === null) {
				continue;
			}

			$asset->setGroup($group);
			$this->assetRepository->persist($asset);
		}

		return $group;
	}
}
