<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use FinGather\Model\Entity\Broker;
use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;

final class BrokerFixture
{
	/** @api */
	public static function getBroker(
		?int $id = null,
		?User $user = null,
		?Portfolio $portfolio = null,
		?string $name = null,
		?BrokerImportTypeEnum $importType = null,
	): Broker {
		$broker = new Broker(
			user: $user ?? UserFixture::getUser(),
			portfolio: $portfolio ?? PortfolioFixture::getPortfolio(),
			name: $name ?? 'Test Broker',
			importType: $importType ?? BrokerImportTypeEnum::Trading212,
		);

		$broker->id = $id ?? 1;

		return $broker;
	}
}
