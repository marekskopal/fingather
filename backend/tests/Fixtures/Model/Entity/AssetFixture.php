<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use FinGather\Model\Entity\Asset;
use FinGather\Model\Entity\Group;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\Ticker;
use FinGather\Model\Entity\User;

class AssetFixture
{
	public static function getAsset(
		?User $user = null,
		?Portfolio $portfolio = null,
		?Ticker $ticker = null,
		?Group $group = null,
		?array $transactions = null,
	): Asset {
		return new Asset(
			user: $user ?? UserFixture::getUser(),
			portfolio: $portfolio ?? PortfolioFixture::getPortfolio(),
			ticker: $ticker ?? TickerFixture::getTicker(),
			group: $group ?? GroupFixture::getGroup(),
			transactions: $transactions ?? [],
		);
	}
}
