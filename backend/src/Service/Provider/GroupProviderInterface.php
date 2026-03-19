<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use Iterator;

interface GroupProviderInterface
{
	/** @return Iterator<Group> */
	public function getGroups(User $user, Portfolio $portfolio): Iterator;

	public function getGroup(User $user, int $groupId): ?Group;

	public function getOthersGroup(User $user, Portfolio $portfolio): Group;

	/** @param list<int> $assetIds */
	public function createGroup(User $user, Portfolio $portfolio, string $name, string $color, array $assetIds): Group;

	public function createOthersGroup(User $user, Portfolio $portfolio): Group;

	/** @param list<int> $assetIds */
	public function updateGroup(Group $group, string $name, string $color, array $assetIds): Group;

	public function deleteGroup(Group $group): void;
}
