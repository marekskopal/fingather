<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use FinGather\Model\Entity\Broker;
use FinGather\Model\Entity\Enum\BrokerImportTypeEnum;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;

final class BrokerFixture
{
	public static function getBroker(
		?User $user = null,
		?Portfolio $portfolio = null,
		?string $name = null,
		?BrokerImportTypeEnum $importType = null,
	): Broker {
		return new Broker(
			user: $user ?? UserFixture::getUser(),
			portfolio: $portfolio ?? PortfolioFixture::getPortfolio(),
			name: $name ?? 'Test Broker',
			importType: $importType ?? BrokerImportTypeEnum::Trading212,
		);
	}
}
