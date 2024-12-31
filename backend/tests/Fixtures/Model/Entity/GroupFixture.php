<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use ArrayIterator;
use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;
use Iterator;

final class GroupFixture
{
	/** @param Iterator<Asset>|null $assets */
	public static function getGroup(
		?User $user = null,
		?Portfolio $portfolio = null,
		?string $name = null,
		?string $color = null,
		?bool $isOthers = null,
		?Iterator $assets = null,
	): Group {
		return new Group(
			user: $user ?? UserFixture::getUser(),
			portfolio: $portfolio ?? PortfolioFixture::getPortfolio(),
			name: $name ?? 'Test Group',
			color: $color ?? '#000000',
			isOthers: $isOthers ?? true,
			assets: $assets ?? new ArrayIterator([]),
		);
	}
}
