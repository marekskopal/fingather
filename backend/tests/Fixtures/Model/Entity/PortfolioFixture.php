<?php

declare(strict_types=1);

namespace FinGather\Tests\Fixtures\Model\Entity;

use FinGather\Model\Entity\Currency;
use FinGather\Model\Entity\Portfolio;
use FinGather\Model\Entity\User;

class PortfolioFixture
{
	public static function getPortfolio(
		?User $user = null,
		?Currency $currency = null,
		?string $name = null,
		?bool $isDefault = null,
	): Portfolio
	{
		return new Portfolio(
			user: $user ?? UserFixture::getUser(),
			currency: $currency ?? CurrencyFixture::getCurrency(),
			name: $name ?? 'Test Portfolio',
			isDefault: $isDefault ?? true,
		);
	}
}
