<?php

declare(strict_types=1);

namespace FinGather\Service\Provider;

use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\User;
use FinGather\Model\Repository\GroupRepository;

class GroupProvider
{
	public function __construct(private readonly GroupRepository $groupRepository)
	{
	}

	/** @return iterable<Group> */
	public function getGroups(User $user): iterable
	{
		return $this->groupRepository->findGroups($user->getId());
	}
}
