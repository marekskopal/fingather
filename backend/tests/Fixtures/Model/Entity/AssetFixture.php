<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\User;

final class AssetFixture
{
	public static function getAsset(
		?int $id = null,
		?User $user = null,
		?Portfolio $portfolio = null,
		?Ticker $ticker = null,
		?Group $group = null,
	): Asset {
		$asset = new Asset(
			user: $user ?? UserFixture::getUser(),
			portfolio: $portfolio ?? PortfolioFixture::getPortfolio(),
			ticker: $ticker ?? TickerFixture::getTicker(),
			group: $group ?? GroupFixture::getGroup(),
		);

		$asset->id = $id ?? 1;

		return $asset;
	}
}
